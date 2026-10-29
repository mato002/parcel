<?php

namespace App\Http\Controllers;

use App\Models\ParcelClaim;
use App\Models\BillingAttempt;
use App\Models\SavedCard;
use App\Models\Transaction;
use App\Services\GatewayReference;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentSettings;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentGatewayManager $gateways, PaymentSettings $settings, SubscriptionBillingService $billing)
    {
        try {
            $paymentGateway = $gateways->for($gateway);
            $data = $paymentGateway->chargeEventFromWebhook($request);
        } catch (RuntimeException $error) {
            return response($error->getMessage(), 401);
        } catch (\InvalidArgumentException $error) {
            return response('Unknown payment gateway', 404);
        }

        if (! $data || empty($data['reference'])) return response()->json(['received' => true]);
        if (($data['status'] ?? null) === 'failed') {
            $failedTransaction = Transaction::where('gateway_reference', $data['reference'])->where('gateway', $gateway)->first();
            if ($failedTransaction) {
                if ($failedTransaction->status === 'success') return response()->json(['received' => true]);
                $failedTransaction->update(['status' => 'failed']);
                $attempt = BillingAttempt::where('transaction_id', $failedTransaction->id)->first();
                if ($attempt && $attempt->status === 'pending') $billing->settleFailure($attempt, $data['message'] ?? 'The payment gateway declined the charge.');
            }
            return response()->json(['received' => true]);
        }
        if (($data['status'] ?? null) !== 'success') return response()->json(['received' => true]);
        $reference = $data['reference'];
        $existingTransaction = Transaction::where('gateway_reference', $reference)->first();
        if ($existingTransaction && $existingTransaction->gateway !== $gateway) return response()->json(['received' => true]);

        // Billing charges already have a transaction and subscription association. They do
        // not carry a public verification token, so settle them before resolving a claim
        // from verification metadata.
        $billingAttempt = $existingTransaction
            ? BillingAttempt::where('transaction_id', $existingTransaction->id)->first()
            : null;
        if ($billingAttempt) {
            DB::transaction(function () use ($existingTransaction, $data, $billingAttempt, $billing) {
                $existingTransaction->update([
                    'status' => 'success',
                    'metadata' => array_merge($existingTransaction->metadata ?? [], ['gateway_response' => $data]),
                ]);
                $billing->settleSuccess($billingAttempt);
            });
            return response()->json(['received' => true]);
        }

        $metadata = $data['metadata'] ?? [];
        $claim = ParcelClaim::where('public_token', $metadata['public_token'] ?? '')->first();
        if (! $claim && isset($metadata['claim_reference'])) $claim = ParcelClaim::where('claim_reference', $metadata['claim_reference'])->first();
        if (! $claim) return response()->json(['received' => true]);

        $newlyVerified = DB::transaction(function () use ($data, $claim, $reference, $gateway, $metadata) {
            $transaction = Transaction::where('gateway_reference', $reference)->first();
            $wasSuccessful = $transaction?->status === 'success';
            $type = $transaction?->type ?? (($metadata['transaction_type'] ?? null) === 'parcel_fee' ? 'charge' : 'verification');
            if ($type !== 'verification') {
                $transaction ??= new Transaction;
                $transaction->fill(['parcel_claim_id' => $claim->id, 'gateway' => $gateway, 'gateway_reference' => $reference,
                    'type' => $type, 'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? app(PaymentSettings::class)->all()['currency'],
                    'status' => 'success', 'description' => 'Parcel fee charge',
                    'metadata' => array_merge($transaction->metadata ?? [], ['gateway_response' => $data])])->save();
                $billingAttempt = BillingAttempt::where('transaction_id', $transaction->id)->first();
                if ($billingAttempt) app(SubscriptionBillingService::class)->settleSuccess($billingAttempt);
                return false;
            }

            $auth = $data['authorization'] ?? [];
            if (! empty($auth['code'])) {
                SavedCard::updateOrCreate(
                    ['parcel_claim_id' => $claim->id, 'gateway' => $gateway, 'authorization_hash' => hash('sha256', $auth['code'])],
                    ['authorization_code' => $auth['code'], 'card_type' => $auth['card_type'] ?? null, 'last4' => $auth['last4'] ?? null,
                        'exp_month' => $auth['exp_month'] ?? null, 'exp_year' => $auth['exp_year'] ?? null,
                        'customer_email' => $data['customer']['email'] ?? $claim->customer_email,
                        'gateway_customer_code' => $data['customer']['code'] ?? null,
                        'reusable' => (bool) ($auth['reusable'] ?? false)]
                );
            }

            $transaction ??= new Transaction;
            $transaction->fill([
                'parcel_claim_id' => $claim->id, 'gateway' => $gateway, 'gateway_reference' => $reference,
                'type' => 'verification', 'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? app(PaymentSettings::class)->all()['verification_currency'],
                'status' => 'success', 'description' => 'Card verification',
                'metadata' => array_merge($transaction->metadata ?? [], ['public_token' => $metadata['public_token'] ?? $claim->public_token]),
            ])->save();
            if ($type === 'verification') $claim->update(['status' => 'verified']);
            return ! $wasSuccessful;
        });

        if ($newlyVerified && $settings->all()['refund_verification']) {
            try {
                $refund = $paymentGateway->refund($reference, null, 'Temporary card verification charge');
                Transaction::firstOrCreate(['gateway_reference' => GatewayReference::make()], [
                    'parcel_claim_id' => $claim->id, 'gateway' => $gateway, 'type' => 'refund', 'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? $settings->all()['verification_currency'],
                    'status' => $refund['status'] ?? 'pending', 'description' => 'Automatic verification refund',
                    'refund_reference' => (string) ($refund['id'] ?? ''), 'metadata' => ['original_reference' => $reference],
                ]);
            } catch (\Throwable $error) {
                report($error);
            }
        }
        return response()->json(['received' => true]);
    }
}
