<?php

namespace App\Http\Controllers;

use App\Mail\RefundConfirmationMail;
use App\Models\ParcelClaim;
use App\Models\SavedCard;
use App\Models\Transaction;
use App\Services\AuditLogService;
use App\Services\AutoBasicSubscriptionEnrollment;
use App\Services\GatewayReference;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentSettings;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }
        if (! config('services.claims.admin_email') || ! hash_equals(strtolower((string) config('services.claims.admin_email')), strtolower((string) Auth::user()->email))) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account is not authorized for the payment admin panel.']);
        }
        $request->session()->regenerate();

        return redirect()->route('admin.index');
    }

    public function forgotPasswordForm()
    {
        return view('admin.forgot-password');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }

    public function resetPasswordForm(Request $request)
    {
        return view('admin.reset-password', ['token' => $request->route('token'), 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function index()
    {
        return $this->panel('overview');
    }

    public function claims()
    {
        return $this->panel('claims');
    }

    public function showClaim(ParcelClaim $claim)
    {
        $claim->load(['cards', 'transactions', 'subscriptions']);

        return view('admin.index', [
            'section' => 'claim_detail', 'claim' => $claim,
            'claims' => collect(), 'cards' => collect(), 'transactions' => collect(),
            'metrics' => ['claims' => 0, 'verified' => 0, 'saved_cards' => 0, 'charge_volume' => 0],
            'paymentSettings' => app(PaymentSettings::class)->all(),
            'availableGateways' => app(PaymentGatewayManager::class)->available(),
            'gatewayConfigured' => app(PaymentGatewayManager::class)->current()->configured(),
        ]);
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
        $transaction->load(['claim.cards', 'card']);
        $transaction->setAttribute('retry_card', $this->retryCardFor($transaction));

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
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
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
            'support_phone' => ['nullable', 'string', 'max:40'],
            'brand_name' => ['required', 'string', 'max:100'],
            'brand_tagline' => ['nullable', 'string', 'max:140'],
            'brand_logo_url' => ['nullable', 'url', 'max:2048'],
            'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_footer_text' => ['nullable', 'string', 'max:255'],
            'checkout_title' => ['required', 'string', 'max:80'],
            'checkout_message' => ['required', 'string', 'max:200'],
            'payment_label' => ['required', 'string', 'max:80'],
            'payment_reference_label' => ['required', 'string', 'max:60'],
        ]);
        $minimumAmount = $settings->minimumAmountMinor($data['verification_currency']) / 100;
        if ((float) $data['verification_amount'] < $minimumAmount) {
            throw ValidationException::withMessages([
                'verification_amount' => 'The minimum payment for '.$data['verification_currency'].' is '.number_format($minimumAmount, 2).'.',
            ]);
        }
        $data['verification_amount'] = (string) round((float) $data['verification_amount'] * 100);
        $data['refund_verification'] = $request->boolean('refund_verification') ? '1' : '0';
        $settings->save($data);

        return redirect()->route('admin.settings')->with('status', 'Settings saved.');
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
            $transactions = Transaction::with(['claim.cards', 'card'])->latest()->get();
            $transactions->each(fn (Transaction $transaction) => $transaction->setAttribute('retry_card', $this->retryCardFor($transaction)));
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

    public function charge(Request $request, SavedCard $card, PaymentGatewayManager $gateways, PaymentSettings $settings, AutoBasicSubscriptionEnrollment $basicEnrollment)
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

            if (($result['status'] ?? null) === 'success') {
                $basicEnrollment->enroll($card->claim);
            }

            return back()->with('status', 'Charge submitted to '.$gateway->name().': '.($result['status'] ?? 'pending'));
        } catch (\Throwable $error) {
            $record->update(['status' => 'failed']);
            report($error);

            return back()->withErrors(['payment' => $gateway->name().' could not process the charge. Check the gateway credentials and try again.']);
        }
    }

    public function retryVerification(Request $request, Transaction $transaction, PaymentGatewayManager $gateways, PaymentSettings $settings)
    {
        abort_unless($transaction->type === 'verification' && in_array($transaction->status, ['pending', 'failed'], true), 422, 'Only pending or failed verification payments can be retried.');
        $claim = $transaction->claim;
        abort_unless($claim, 422, 'The customer record for this payment is no longer available.');
        $card = $this->retryCardFor($transaction);
        abort_unless($card, 422, 'No reusable saved card is available for this customer.');

        $minimum = $settings->minimumAmountMinor($transaction->currency) / 100;
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$minimum, 'max:1000000'],
        ], ['amount.min' => 'The minimum payment for '.$transaction->currency.' is '.number_format($minimum, 2).'.']);

        $gateway = $gateways->for($transaction->gateway);
        try {
            $original = $gateway->verify($transaction->gateway_reference);
        } catch (\Throwable $error) {
            report($error);

            return back()->withErrors(['retry' => 'Could not confirm the previous attempt with the payment provider. Try again when the provider is reachable.']);
        }

        $originalStatus = strtolower((string) ($original['status'] ?? 'pending'));
        if ($originalStatus === 'success') {
            return back()->withErrors(['retry' => 'The previous payment is confirmed successful. The card was not charged again.']);
        }
        if (! in_array($originalStatus, ['failed', 'abandoned'], true)) {
            return back()->withErrors(['retry' => 'The previous payment is still '.$originalStatus.'. Wait for Paystack to confirm it failed before retrying.']);
        }

        $transaction->update([
            'status' => 'failed',
            'metadata' => array_merge($transaction->metadata ?? [], ['gateway_response' => $original]),
        ]);

        $latestRetry = Transaction::where('metadata->retry_of_reference', $transaction->gateway_reference)
            ->latest()
            ->first();
        if ($latestRetry && in_array($latestRetry->status, ['pending', 'success'], true)) {
            return back()->withErrors(['retry' => 'A retry is already '.$latestRetry->status.' ('.$latestRetry->gateway_reference.'). Wait for its final status before trying again.']);
        }

        $amount = (int) round((float) $data['amount'] * 100);
        $reference = GatewayReference::make();
        $retry = Transaction::create([
            'parcel_claim_id' => $claim->id,
            'saved_card_id' => $card->id,
            'gateway' => $transaction->gateway,
            'gateway_reference' => $reference,
            'type' => 'verification',
            'amount' => $amount,
            'currency' => $transaction->currency,
            'status' => 'pending',
            'description' => 'Retry of '.$transaction->gateway_reference,
            'metadata' => [
                'public_token' => $claim->public_token,
                'claim_reference' => $claim->claim_reference,
                'retry_of_reference' => $transaction->gateway_reference,
                'admin_initiated' => true,
            ],
        ]);

        try {
            $result = $gateway->chargeAuthorization(
                $card->authorization_code,
                $card->customer_email ?: $claim->customer_email,
                $amount,
                $reference,
                ['public_token' => $claim->public_token, 'claim_reference' => $claim->claim_reference, 'admin_retry' => true],
                $transaction->currency
            );
            if (in_array(strtolower((string) ($result['status'] ?? '')), ['failed', 'abandoned'], true)) {
                $retry->update(['status' => 'failed']);
            }
            $retry->refresh();
            $retry->update(['metadata' => array_merge($retry->metadata ?? [], ['gateway_response' => $result])]);

            return redirect()->route('admin.transactions.show', $transaction)->with('status', 'Retry submitted for '.$retry->gateway_reference.'. Provider status: '.($result['status'] ?? 'pending').'.');
        } catch (\Throwable $error) {
            report($error);
            $retry->refresh();
            if ($retry->status === 'pending') {
                $retry->update(['status' => 'failed', 'metadata' => array_merge($retry->metadata ?? [], ['gateway_error' => $error->getMessage()])]);
            }

            return back()->withErrors(['retry' => 'Paystack could not charge the saved card: '.$error->getMessage()]);
        }
    }

    private function retryCardFor(Transaction $transaction): ?SavedCard
    {
        if ($transaction->card?->reusable && $transaction->card->gateway === $transaction->gateway) {
            return $transaction->card;
        }

        return $transaction->claim?->cards->first(fn (SavedCard $card) => $card->reusable && $card->gateway === $transaction->gateway
        ) ?? SavedCard::where('parcel_claim_id', $transaction->parcel_claim_id)
            ->where('gateway', $transaction->gateway)
            ->where('reusable', true)
            ->latest()
            ->first();
    }

    public function refund(Request $request, Transaction $transaction, PaymentGatewayManager $gateways)
    {
        abort_unless(in_array($transaction->type, ['charge', 'verification'], true) && $transaction->status === 'success', 422, 'Only successful charges can be refunded.');
        $data = $request->validate(['amount' => ['nullable', 'numeric', 'gt:0']]);
        $amount = isset($data['amount']) ? (int) round((float) $data['amount'] * 100) : null;
        if ($amount && $amount > $transaction->amount) {
            return back()->withErrors(['amount' => 'Refund cannot exceed the original charge.']);
        }
        try {
            $gateway = $gateways->for($transaction->gateway);
            $result = $gateway->refund($transaction->gateway_reference, $amount, 'Payment refund');
            Transaction::create(['parcel_claim_id' => $transaction->parcel_claim_id, 'saved_card_id' => $transaction->saved_card_id,
                'gateway' => $transaction->gateway, 'gateway_reference' => GatewayReference::make(), 'type' => 'refund', 'amount' => $amount ?? $transaction->amount,
                'currency' => $transaction->currency, 'status' => $result['status'] ?? 'pending', 'description' => 'Refund for '.$transaction->gateway_reference,
                'refund_reference' => (string) ($result['id'] ?? $result['transaction'] ?? ''), 'metadata' => ['original_reference' => $transaction->gateway_reference]]);

            $claim = $transaction->claim;
            if ($claim && $claim->customer_email && ! str_ends_with($claim->customer_email, '@verification.invalid')) {
                Mail::to($claim->customer_email)->send(new RefundConfirmationMail(
                    $claim,
                    $amount ?? $transaction->amount,
                    $transaction->currency
                ));
            }

            return back()->with('status', 'Refund request sent to '.$gateway->name().'.');
        } catch (\Throwable $error) {
            report($error);

            return back()->withErrors(['refund' => 'The configured payment gateway could not process the refund.']);
        }
    }
}
