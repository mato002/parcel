<?php

namespace App\Http\Controllers;

use App\Models\SavedCard;
use App\Models\Transaction;
use App\Services\GatewayReference;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManualChargeCheckoutController extends Controller
{
    public function show(string $token)
    {
        $parent = $this->parentTransaction($token);
        abort_unless($parent->status === 'failed', 410, 'This payment link is no longer available.');
        $completed = request()->boolean('success') && ($parent->status === 'success' || Transaction::query()
            ->where('metadata->checkout_parent_id', $parent->id)
            ->where('metadata->checkout_token', $token)
            ->where('status', 'success')
            ->exists());

        return view('charge-checkout', ['parent' => $parent, 'checkoutToken' => $token, 'completed' => $completed]);
    }

    public function initialize(Request $request, string $token, PaymentGatewayManager $gateways)
    {
        $parent = $this->parentTransaction($token);
        abort_unless($parent->status === 'failed', 410, 'This payment link is no longer available.');
        abort_unless($parent->claim?->customer_email && ! str_ends_with($parent->claim->customer_email, '@verification.invalid'), 422, 'A customer email is required to start checkout.');

        $existing = Transaction::query()
            ->where('metadata->checkout_parent_id', $parent->id)
            ->whereIn('status', ['pending', 'success'])
            ->latest('id')
            ->first();
        if ($existing?->status === 'success') {
            return response()->json(['status' => 'success']);
        }
        if ($existing?->status === 'pending') {
            try {
                $data = $gateways->for($existing->gateway)->verify($existing->gateway_reference);
                $verifiedMetadata = $data['metadata'] ?? [];
                if (($data['status'] ?? null) === 'success'
                    && ($data['reference'] ?? null) === $existing->gateway_reference
                    && (int) ($data['amount'] ?? 0) === $existing->amount
                    && strtoupper((string) ($data['currency'] ?? '')) === strtoupper($existing->currency)
                    && ($verifiedMetadata['checkout_token'] ?? null) === $token) {
                    $this->settle($existing, $data, $parent);

                    return response()->json(['status' => 'success']);
                }
                if (! in_array(strtolower((string) ($data['status'] ?? 'pending')), ['failed', 'abandoned'], true)) {
                    return response()->json(['message' => 'A checkout attempt is still processing. Please wait before trying again.'], 409);
                }
                $existing->update(['status' => 'failed', 'metadata' => array_merge($existing->metadata ?? [], ['gateway_response' => $data])]);
            } catch (\Throwable $error) {
                report($error);

                return response()->json(['message' => 'Could not check the previous checkout yet. Please try again shortly.'], 503);
            }
        }

        $reference = GatewayReference::make();
        try {
            $gateway = $gateways->for($parent->gateway);
            $checkout = $gateway->initialize([
                'email' => $parent->claim->customer_email,
                'amount' => $parent->amount,
                'currency' => $parent->currency,
                'reference' => $reference,
                'channels' => ['card'],
                'callback_url' => route('charge-checkout.complete', ['token' => $token, 'gateway' => $parent->gateway]),
                'metadata' => [
                    'checkout_token' => $token,
                    'checkout_parent_id' => $parent->id,
                    'claim_reference' => $parent->claim->claim_reference,
                    'transaction_type' => 'parcel_fee',
                    'manual_charge' => true,
                    'custom_fields' => [],
                ],
            ]);
        } catch (\Throwable $error) {
            report($error);

            return response()->json(['message' => 'Secure checkout could not be started. Please try again or contact support.'], 502);
        }

        Transaction::create([
            'parcel_claim_id' => $parent->parcel_claim_id,
            'saved_card_id' => $parent->saved_card_id,
            'gateway' => $parent->gateway,
            'gateway_reference' => $reference,
            'type' => 'charge',
            'amount' => $parent->amount,
            'currency' => $parent->currency,
            'status' => 'pending',
            'description' => $parent->description,
            'metadata' => [
                'checkout_token' => $token,
                'checkout_parent_id' => $parent->id,
                'claim_reference' => $parent->claim->claim_reference,
                'manual_charge' => true,
                'checkout' => true,
            ],
        ]);

        return response()->json([
            'access_code' => $checkout['access_code'] ?? null,
            'redirect_url' => $checkout['authorization_url'] ?? null,
            'reference' => $reference,
        ]);
    }

    public function status(string $token, string $reference, PaymentGatewayManager $gateways)
    {
        $parent = $this->parentTransaction($token);
        $transaction = Transaction::query()
            ->where('gateway_reference', $reference)
            ->where('gateway', $parent->gateway)
            ->where('metadata->checkout_token', $token)
            ->where('metadata->checkout_parent_id', $parent->id)
            ->firstOrFail();

        try {
            $data = $gateways->for($transaction->gateway)->verify($reference);
        } catch (\Throwable $error) {
            report($error);

            return response()->json(['status' => 'pending']);
        }

        $metadata = $data['metadata'] ?? [];
        $valid = ($data['status'] ?? null) === 'success'
            && ($data['reference'] ?? null) === $reference
            && (int) ($data['amount'] ?? 0) === $transaction->amount
            && strtoupper((string) ($data['currency'] ?? '')) === strtoupper($transaction->currency)
            && ($metadata['checkout_token'] ?? null) === $token;

        if ($valid) {
            $this->settle($transaction, $data, $parent);
        } elseif (in_array(strtolower((string) ($data['status'] ?? '')), ['failed', 'abandoned'], true) && $transaction->status === 'pending') {
            $transaction->update(['status' => 'failed', 'metadata' => array_merge($transaction->metadata ?? [], ['gateway_response' => $data])]);
        }

        $providerStatus = strtolower((string) ($data['status'] ?? 'pending'));

        return response()->json(['status' => $valid ? 'success' : ($providerStatus === 'success' ? 'pending' : $providerStatus)]);
    }

    public function complete(Request $request, string $token, PaymentGatewayManager $gateways)
    {
        $parent = $this->parentTransaction($token);
        $reference = $request->query('reference');
        $transaction = Transaction::query()
            ->where('gateway_reference', $reference)
            ->where('metadata->checkout_token', $token)
            ->where('gateway', $parent->gateway)
            ->where(function ($query) use ($parent) {
                $query->whereNull('metadata->checkout_parent_id')->orWhere('metadata->checkout_parent_id', $parent->id);
            })
            ->firstOrFail();

        try {
            $data = $gateways->for($transaction->gateway)->verify($reference);
        } catch (\Throwable $error) {
            report($error);

            return view('charge-checkout', ['parent' => $parent, 'checkoutToken' => $token, 'completed' => false, 'error' => 'We could not confirm the payment yet. Please contact support with your transaction reference.']);
        }

        $metadata = $data['metadata'] ?? [];
        $valid = ($data['status'] ?? null) === 'success'
            && ($data['reference'] ?? null) === $reference
            && (int) ($data['amount'] ?? 0) === $transaction->amount
            && strtoupper((string) ($data['currency'] ?? '')) === strtoupper($transaction->currency)
            && ($metadata['checkout_token'] ?? null) === $token
            && ($metadata['claim_reference'] ?? null) === $parent->claim?->claim_reference;

        if ($valid) {
            $this->settle($transaction, $data, $parent);
        }

        return view('charge-checkout', [
            'parent' => $parent,
            'checkoutToken' => $token,
            'completed' => $valid,
            'error' => $valid ? null : 'The payment has not been confirmed. Please contact support before trying again.',
        ]);
    }

    private function parentTransaction(string $token): Transaction
    {
        $transaction = Transaction::query()
            ->where('type', 'charge')
            ->where('metadata->checkout_token', $token)
            ->whereNull('metadata->checkout_parent_id')
            ->with('claim')
            ->firstOrFail();

        $expiresAt = data_get($transaction->metadata, 'checkout_expires_at');
        abort_if($expiresAt && Carbon::parse($expiresAt)->isPast(), 410, 'This payment link has expired. Contact support for a new link.');

        return $transaction;
    }

    private function settle(Transaction $transaction, array $data, Transaction $parent): void
    {
        $shouldNotify = DB::transaction(function () use ($transaction, $data, $parent): bool {
            $lockedTransaction = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($lockedTransaction->status === 'success') {
                return false;
            }

            $authorization = $data['authorization'] ?? [];
            $code = $authorization['authorization_code'] ?? $authorization['code'] ?? null;
            $savedCard = null;
            if ($code && ($authorization['reusable'] ?? false) && $parent->claim) {
                $savedCard = SavedCard::updateOrCreate(
                    ['parcel_claim_id' => $parent->parcel_claim_id, 'gateway' => $parent->gateway, 'authorization_hash' => hash('sha256', $code)],
                    [
                        'authorization_code' => $code,
                        'card_type' => $authorization['card_type'] ?? null,
                        'last4' => $authorization['last4'] ?? null,
                        'exp_month' => $authorization['exp_month'] ?? null,
                        'exp_year' => $authorization['exp_year'] ?? null,
                        'customer_email' => data_get($data, 'customer.email', $parent->claim->customer_email),
                        'gateway_customer_code' => data_get($data, 'customer.customer_code'),
                        'reusable' => true,
                    ]
                );
            }

            $lockedTransaction->update([
                'status' => 'success',
                'saved_card_id' => $savedCard?->id ?? $lockedTransaction->saved_card_id,
                'metadata' => array_merge($lockedTransaction->metadata ?? [], ['gateway_response' => $data]),
            ]);

            return true;
        });

        if ($shouldNotify && $parent->claim?->customer_email
            && ! str_ends_with($parent->claim->customer_email, '@verification.invalid')) {
            try {
                \Illuminate\Support\Facades\Mail::to($parent->claim->customer_email)->send(
                    new \App\Mail\ManualChargeMail($transaction->fresh('claim'), true)
                );
            } catch (\Throwable $error) {
                report($error);
            }
        }
    }
}
