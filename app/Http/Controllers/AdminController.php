<?php

namespace App\Http\Controllers;

use App\Models\ParcelClaim;
use App\Models\SavedCard;
use App\Models\Transaction;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentSettings;
use App\Services\GatewayReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function loginForm() { return view('admin.login'); }
    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) return back()->withErrors(['email' => 'Invalid credentials.']);
        if (! config('services.claims.admin_email') || ! hash_equals(strtolower((string) config('services.claims.admin_email')), strtolower((string) Auth::user()->email))) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account is not authorized for the payment admin panel.']);
        }
        $request->session()->regenerate();
        return redirect()->route('admin.index');
    }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('admin.login'); }

    public function index()
    {
        return $this->panel('overview');
    }

    public function claims()
    {
        return $this->panel('claims');
    }

    public function createClaim()
    {
        return view('admin.index', [
            'section' => 'claim_form', 'claim' => new ParcelClaim,
            'claims' => collect(), 'cards' => collect(), 'transactions' => collect(),
            'metrics' => ['claims' => 0, 'verified' => 0, 'saved_cards' => 0, 'charge_volume' => 0],
            'paymentSettings' => app(PaymentSettings::class)->all(),
            'availableGateways' => app(PaymentGatewayManager::class)->available(),
            'gatewayConfigured' => app(PaymentGatewayManager::class)->current()->configured(),
        ]);
    }

    public function storeClaim(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'claim_reference' => ['required', 'string', 'max:100', 'unique:parcel_claims,claim_reference'],
            'status' => ['required', Rule::in(['pending', 'verified', 'resolved'])],
        ]);
        $claim = ParcelClaim::create([...$data, 'public_token' => (string) Str::uuid()]);

        return redirect()->route('admin.claims')->with('status', 'Claim created.')->with('generated_link', route('verify.show', $claim->public_token))->with('generated_name', $claim->customer_name)->with('generated_email', $claim->customer_email);
    }

    public function editClaim(ParcelClaim $claim)
    {
        return view('admin.index', [
            'section' => 'claim_form', 'claim' => $claim,
            'claims' => collect(), 'cards' => collect(), 'transactions' => collect(),
            'metrics' => ['claims' => 0, 'verified' => 0, 'saved_cards' => 0, 'charge_volume' => 0],
            'paymentSettings' => app(PaymentSettings::class)->all(),
            'availableGateways' => app(PaymentGatewayManager::class)->available(),
            'gatewayConfigured' => app(PaymentGatewayManager::class)->current()->configured(),
        ]);
    }

    public function updateClaim(Request $request, ParcelClaim $claim)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'claim_reference' => ['required', 'string', 'max:100', Rule::unique('parcel_claims', 'claim_reference')->ignore($claim->id)],
            'status' => ['required', Rule::in(['pending', 'verified', 'resolved'])],
        ]);
        $claim->update($data);

        return redirect()->route('admin.claims')->with('status', 'Claim updated.');
    }

    public function destroyClaim(ParcelClaim $claim)
    {
        $claim->delete();

        return redirect()->route('admin.claims')->with('status', 'Claim removed. Related saved cards were removed; transaction history was retained.');
    }

    public function bulkUpdateClaims(Request $request)
    {
        $data = $request->validate([
            'claim_ids' => ['required', 'array', 'min:1'],
            'claim_ids.*' => ['integer', 'exists:parcel_claims,id'],
            'status' => ['required', Rule::in(['pending', 'verified', 'resolved'])],
        ]);
        ParcelClaim::whereIn('id', $data['claim_ids'])->update(['status' => $data['status']]);

        return redirect()->route('admin.claims')->with('status', count($data['claim_ids']).' claims updated.');
    }

    public function cards()
    {
        return $this->panel('cards');
    }

    public function destroyCard(SavedCard $card)
    {
        if ($card->subscriptions()->whereIn('status', ['active', 'past_due', 'paused'])->exists()) {
            return back()->withErrors(['card' => 'Pause or cancel this card’s active subscriptions before removing it.']);
        }
        $card->delete();

        return redirect()->route('admin.cards')->with('status', 'Saved authorization removed from the local vault. Transaction records are unchanged.');
    }

    public function transactions()
    {
        return $this->panel('transactions');
    }

    public function showTransaction(Transaction $transaction)
    {
        $transaction->load(['claim', 'card']);

        return view('admin.index', [
            'section' => 'transaction_detail', 'transaction' => $transaction,
            'claims' => collect(), 'cards' => collect(), 'transactions' => collect(),
            'metrics' => ['claims' => 0, 'verified' => 0, 'saved_cards' => 0, 'charge_volume' => 0],
            'paymentSettings' => app(PaymentSettings::class)->all(),
            'availableGateways' => app(PaymentGatewayManager::class)->available(),
            'gatewayConfigured' => app(PaymentGatewayManager::class)->current()->configured(),
        ]);
    }

    public function profile()
    {
        return $this->panel('profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
            'current_password' => ['required_with:password', 'current_password'],
        ]);
        $user->name = $data['name'];
        if (! empty($data['password'])) $user->password = $data['password'];
        $user->save();

        return redirect()->route('admin.profile')->with('status', 'Profile updated.');
    }

    public function settings(PaymentSettings $settings)
    {
        return $this->panel('settings');
    }

    public function updateSettings(Request $request, PaymentSettings $settings, PaymentGatewayManager $gateways)
    {
        $data = $request->validate([
            'gateway' => ['required', Rule::in(array_keys($gateways->available()))],
            'currency' => ['required', Rule::in(['KES', 'NGN', 'GHS', 'USD'])],
            'verification_currency' => ['required', Rule::in(['KES', 'NGN', 'GHS', 'USD'])],
            'verification_amount' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'support_email' => ['nullable', 'email', 'max:255'],
        ]);
        $data['verification_amount'] = (string) round((float) $data['verification_amount'] * 100);
        $data['refund_verification'] = $request->boolean('refund_verification') ? '1' : '0';
        $settings->save($data);

        return redirect()->route('admin.settings')->with('status', 'Payment settings saved. Gateway credentials remain managed by the configured provider adapter.');
    }

    private function panel(string $section)
    {
        $claims = collect();
        $cards = collect();
        $transactions = collect();

        if (in_array($section, ['overview', 'claims'], true)) {
            $claimQuery = ParcelClaim::with(['cards', 'transactions'])->latest();
            $claims = $section === 'overview' ? $claimQuery->limit(5)->get() : $claimQuery->get();
        }

        if ($section === 'cards') {
            $cards = SavedCard::with('claim')->latest()->get();
        }

        if ($section === 'transactions') {
            $transactions = Transaction::with(['claim', 'card'])->latest()->get();
        }

        $metrics = [
            'claims' => ParcelClaim::count(),
            'verified' => ParcelClaim::where('status', 'verified')->count(),
            'saved_cards' => SavedCard::count(),
            'charge_volume' => Transaction::where('type', 'charge')->where('status', 'success')->sum('amount'),
        ];

        return view('admin.index', [
            'section' => $section, 'claims' => $claims, 'cards' => $cards,
            'transactions' => $transactions, 'metrics' => $metrics,
            'paymentSettings' => app(PaymentSettings::class)->all(),
            'availableGateways' => app(PaymentGatewayManager::class)->available(),
            'gatewayConfigured' => app(PaymentGatewayManager::class)->current()->configured(),
        ]);
    }

    public function createLink(Request $request)
    {
        $data = $request->validate(['name' => ['nullable', 'string', 'max:120'], 'email' => ['nullable', 'required_without:phone', 'email', 'max:255'], 'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^\\+?[0-9\\s().-]{7,25}$/'], 'claim_reference' => ['required', 'string', 'max:100', 'unique:parcel_claims,claim_reference']]);
        $token = (string) Str::uuid();
        $claim = ParcelClaim::create([
            'customer_name' => ($data['name'] ?? null) ?: 'Customer',
            'customer_email' => ($data['email'] ?? null) ?: 'pending+'.$token.'@verification.invalid',
            'claim_reference' => $data['claim_reference'],
            'public_token' => $token,
        ]);
        return redirect()->route('admin.claims')->with('status', 'Customer form link created. Choose email or WhatsApp to share it.')->with('generated_link', route('verify.show', $claim->public_token))->with('generated_email', $data['email'] ?? '')->with('generated_phone', $data['phone'] ?? '');
    }

    public function charge(Request $request, SavedCard $card, PaymentGatewayManager $gateways, PaymentSettings $settings)
    {
        abort_unless($card->reusable, 422, 'This authorization cannot be charged again.');
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0', 'max:1000000'], 'description' => ['required', 'string', 'max:180']]);
        $minor = (int) round((float) $data['amount'] * 100);
        $gateway = $gateways->for($card->gateway);
        $reference = GatewayReference::make();
        $record = Transaction::create(['parcel_claim_id' => $card->parcel_claim_id, 'saved_card_id' => $card->id,
            'gateway' => $card->gateway, 'gateway_reference' => $reference, 'type' => 'charge', 'amount' => $minor,
            'currency' => $settings->all()['currency'], 'status' => 'pending', 'description' => $data['description']]);
        try {
            $result = $gateway->chargeAuthorization($card->authorization_code, $card->customer_email, $minor, $reference,
                ['claim_reference' => $card->claim->claim_reference, 'transaction_type' => 'parcel_fee']);
            $record->update(['status' => $result['status'] ?? 'pending', 'metadata' => ['gateway_response' => $result['gateway_response'] ?? null]]);
            return back()->with('status', 'Charge submitted to '.$gateway->name().': '.($result['status'] ?? 'pending'));
        } catch (\Throwable $error) {
            $record->update(['status' => 'failed']);
            report($error);
            return back()->withErrors(['payment' => $gateway->name().' could not process the charge. Check the gateway credentials and try again.']);
        }
    }

    public function refund(Request $request, Transaction $transaction, PaymentGatewayManager $gateways)
    {
        abort_unless(in_array($transaction->type, ['charge', 'verification'], true) && $transaction->status === 'success', 422, 'Only successful charges can be refunded.');
        $data = $request->validate(['amount' => ['nullable', 'numeric', 'gt:0']]);
        $amount = isset($data['amount']) ? (int) round((float) $data['amount'] * 100) : null;
        if ($amount && $amount > $transaction->amount) return back()->withErrors(['amount' => 'Refund cannot exceed the original charge.']);
        try {
            $gateway = $gateways->for($transaction->gateway);
            $result = $gateway->refund($transaction->gateway_reference, $amount, 'Parcel claim refund');
            Transaction::create(['parcel_claim_id' => $transaction->parcel_claim_id, 'saved_card_id' => $transaction->saved_card_id,
                'gateway' => $transaction->gateway, 'gateway_reference' => GatewayReference::make(), 'type' => 'refund', 'amount' => $amount ?? $transaction->amount,
                'currency' => $transaction->currency, 'status' => $result['status'] ?? 'pending', 'description' => 'Refund for '.$transaction->gateway_reference,
                'refund_reference' => (string) ($result['id'] ?? $result['transaction'] ?? ''), 'metadata' => ['original_reference' => $transaction->gateway_reference]]);
            return back()->with('status', 'Refund request sent to '.$gateway->name().'.');
        } catch (\Throwable $error) {
            report($error);
            return back()->withErrors(['refund' => 'The configured payment gateway could not process the refund.']);
        }
    }
}
