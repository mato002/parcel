<?php

namespace App\Http\Controllers;

use App\Models\BillingProduct;
use App\Models\BillingSubscription;
use App\Models\ParcelClaim;
use App\Models\SavedCard;
use App\Models\Transaction;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentSettings;
use App\Services\SubscriptionBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(PaymentSettings $settings)
    {
        return $this->page('billing', [
            'billingProducts' => BillingProduct::withCount('subscriptions')->latest()->get(),
            'subscriptions' => BillingSubscription::with(['claim', 'card', 'product', 'attempts.transaction'])->latest()->get(),
        ], $settings);
    }

    public function createProduct(PaymentSettings $settings)
    {
        return $this->page('billing_product_form', ['billingProduct' => new BillingProduct(['active' => true, 'interval_count' => 1])], $settings);
    }

    public function editProduct(BillingProduct $product, PaymentSettings $settings)
    {
        return $this->page('billing_product_form', ['billingProduct' => $product], $settings);
    }

    public function storeProduct(Request $request, PaymentSettings $settings)
    {
        BillingProduct::create($this->validateProduct($request));
        return redirect()->route('admin.billing')->with('status', 'Billing plan created.');
    }

    public function updateProduct(Request $request, BillingProduct $product)
    {
        $product->update($this->validateProduct($request));
        return redirect()->route('admin.billing')->with('status', 'Billing plan updated. Existing subscriptions keep their agreed amount and interval.');
    }

    public function destroyProduct(BillingProduct $product)
    {
        if ($product->subscriptions()->whereIn('status', ['active', 'past_due', 'paused'])->exists()) {
            return back()->withErrors(['product' => 'Cancel or complete this plan’s active subscriptions before deleting it.']);
        }
        $product->delete();
        return redirect()->route('admin.billing')->with('status', 'Billing plan deleted.');
    }

    public function createSubscription(PaymentSettings $settings)
    {
        return $this->page('subscription_form', [
            'billingProducts' => BillingProduct::where('active', true)->orderBy('name')->get(),
            'billingClaims' => ParcelClaim::whereHas('cards', fn ($query) => $query->where('reusable', true))
                ->with(['cards' => fn ($query) => $query->where('reusable', true)])->orderBy('customer_name')->get(),
        ], $settings);
    }

    public function storeSubscription(Request $request, PaymentSettings $settings)
    {
        $data = $request->validate([
            'parcel_claim_id' => ['required', 'integer', 'exists:parcel_claims,id'],
            'saved_card_id' => ['required', 'integer', 'exists:saved_cards,id'],
            'billing_product_id' => ['required', 'integer', 'exists:billing_products,id'],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'consent_record' => ['required', 'string', 'min:10', 'max:1500'],
            'consent_confirmed' => ['accepted'],
            'charge_now' => ['nullable', 'boolean'],
        ]);

        $claim = ParcelClaim::findOrFail($data['parcel_claim_id']);
        $card = SavedCard::whereKey($data['saved_card_id'])->where('parcel_claim_id', $claim->id)->where('reusable', true)->firstOrFail();
        $product = BillingProduct::whereKey($data['billing_product_id'])->where('active', true)->firstOrFail();
        $start = Carbon::parse($data['starts_at']);
        $subscription = BillingSubscription::create([
            'parcel_claim_id' => $claim->id,
            'saved_card_id' => $card->id,
            'billing_product_id' => $product->id,
            'product_name' => $product->name,
            'amount' => $product->amount,
            'currency' => $product->currency,
            'interval_unit' => $product->interval_unit,
            'interval_count' => $product->interval_count,
            'status' => 'active',
            'starts_at' => $start,
            'next_billing_at' => $start,
            'consented_at' => now(),
            'consent_record' => $data['consent_record'],
            'consented_by' => $request->user()->id,
        ]);

        if ($request->boolean('charge_now') && $start->lessThanOrEqualTo(now())) {
            app(SubscriptionBillingService::class)->bill($subscription, scheduled: true);
            $subscription->refresh();
            $message = $subscription->last_billed_at
                ? 'Subscription created and first payment succeeded.'
                : 'Subscription created. The first payment needs attention; review its status and attempt history.';
        } else {
            $message = 'Subscription created and scheduled to start on '.$start->format('M j, Y g:i A').'.';
        }

        return redirect()->route('admin.billing')->with('status', $message);
    }

    public function chargeNow(BillingSubscription $subscription, SubscriptionBillingService $billing)
    {
        if (! in_array($subscription->status, ['active', 'past_due'], true)) {
            return back()->withErrors(['billing' => 'Only active or past-due subscriptions can be charged.']);
        }
        $charged = $billing->bill($subscription);
        $subscription->refresh();
        return back()->with('status', $charged ? 'Subscription payment succeeded.' : 'Charge attempt recorded. Current subscription status: '.str_replace('_', ' ', $subscription->status).'.');
    }

    public function updateStatus(Request $request, BillingSubscription $subscription)
    {
        $data = $request->validate(['action' => ['required', Rule::in(['pause', 'resume', 'cancel'])]]);
        if ($data['action'] === 'pause') {
            abort_unless(in_array($subscription->status, ['active', 'past_due'], true), 422, 'Only active or past-due subscriptions can be paused.');
            $subscription->update(['status' => 'paused', 'retry_at' => null]);
            $message = 'Subscription paused.';
        } elseif ($data['action'] === 'resume') {
            abort_unless($subscription->status === 'paused', 422, 'Only paused subscriptions can be resumed.');
            $subscription->update(['status' => 'active', 'next_billing_at' => $subscription->next_billing_at ?? now(), 'retry_at' => null, 'failed_attempts' => 0]);
            $message = 'Subscription resumed.';
        } else {
            abort_unless(! in_array($subscription->status, ['cancelled', 'completed'], true), 422, 'This subscription is already closed.');
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'next_billing_at' => null, 'retry_at' => null]);
            $message = 'Subscription cancelled. No further automatic charges will be made.';
        }
        return back()->with('status', $message);
    }

    private function validateProduct(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'currency' => ['required', Rule::in(['KES', 'NGN', 'GHS', 'USD'])],
            'interval_unit' => ['required', Rule::in(['once', 'day', 'week', 'month', 'year'])],
            'interval_count' => ['required', 'integer', 'min:1', 'max:36'],
        ]);
        $data['amount'] = (int) round((float) $data['amount'] * 100);
        $data['active'] = $request->boolean('active');
        if ($data['interval_unit'] === 'once') $data['interval_count'] = 1;
        return $data;
    }

    private function page(string $section, array $data, PaymentSettings $settings)
    {
        $gateways = app(PaymentGatewayManager::class);
        return view('admin.index', array_merge([
            'section' => $section,
            'claim' => new ParcelClaim,
            'claims' => collect(), 'cards' => collect(), 'transactions' => collect(),
            'metrics' => [
                'claims' => ParcelClaim::count(),
                'verified' => ParcelClaim::where('status', 'verified')->count(),
                'saved_cards' => SavedCard::count(),
                'charge_volume' => Transaction::where('type', 'charge')->where('status', 'success')->sum('amount'),
            ],
            'paymentSettings' => $settings->all(),
            'availableGateways' => $gateways->available(),
            'gatewayConfigured' => $gateways->current()->configured(),
        ], $data));
    }
}
