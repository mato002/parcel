<?php

namespace App\Services;

use App\Models\BillingAttempt;
use App\Models\BillingSubscription;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SubscriptionBillingService
{
    public function bill(BillingSubscription $subscription, bool $scheduled = false): bool
    {
        $lock = Cache::lock('subscription-billing:'.$subscription->id, 90);
        if (! $lock->get()) return false;

        try {
            $subscription->refresh();
            if (! in_array($subscription->status, ['active', 'past_due'], true)) return false;
            if (! $subscription->card || ! $subscription->card->reusable || ! $subscription->card->authorization_code) {
                $subscription->update(['status' => 'past_due', 'retry_at' => null]);
                return false;
            }

            $scheduledFor = $scheduled
                ? ($subscription->retry_at ?? $subscription->next_billing_at)
                : now();
            if (! $scheduledFor || ($scheduled && $scheduledFor->isFuture())) return false;

            $attemptNumber = $subscription->failed_attempts + 1;
            $dedupeKey = $scheduled
                ? 'subscription-'.$subscription->id.'-'.$scheduledFor->timestamp.'-'.$attemptNumber
                : 'subscription-'.$subscription->id.'-manual-'.Str::uuid();

            $attempt = DB::transaction(function () use ($subscription, $scheduledFor, $attemptNumber, $dedupeKey) {
                $existing = BillingAttempt::where('dedupe_key', $dedupeKey)->first();
                if ($existing) return null;
                if (BillingAttempt::where('billing_subscription_id', $subscription->id)->where('status', 'pending')->exists()) return null;

                $transaction = Transaction::create([
                    'parcel_claim_id' => $subscription->parcel_claim_id,
                    'saved_card_id' => $subscription->saved_card_id,
                    'gateway' => $subscription->card->gateway,
                    'gateway_reference' => GatewayReference::make(),
                    'type' => 'charge', 'amount' => $subscription->amount,
                    'currency' => $subscription->currency, 'status' => 'pending',
                    'description' => 'Subscription: '.$subscription->product_name,
                    'metadata' => ['subscription_id' => $subscription->id, 'consented_at' => $subscription->consented_at?->toIso8601String()],
                ]);

                return BillingAttempt::create([
                    'billing_subscription_id' => $subscription->id,
                    'transaction_id' => $transaction->id,
                    'scheduled_for' => $scheduledFor,
                    'dedupe_key' => $dedupeKey,
                    'attempt_number' => $attemptNumber,
                    'status' => 'pending',
                ]);
            });

            if (! $attempt) return false;

            try {
                $transaction = $attempt->transaction;
                $result = app(PaymentGatewayManager::class)->for($subscription->card->gateway)->chargeAuthorization(
                    $subscription->card->authorization_code,
                    $subscription->card->customer_email,
                    $subscription->amount,
                    $transaction->gateway_reference,
                    ['subscription_id' => $subscription->id, 'billing_attempt_id' => $attempt->id, 'transaction_type' => 'parcel_fee'],
                    $subscription->currency,
                );

                $status = $result['status'] ?? 'pending';
                if ($status === 'success') {
                    $transaction->update(['status' => 'success', 'metadata' => ['subscription_id' => $subscription->id, 'billing_attempt_id' => $attempt->id, 'gateway_response' => $result]]);
                    $this->settleSuccess($attempt->fresh());
                    return true;
                }

                if ($status === 'failed') {
                    $this->settleFailure($attempt->fresh(), $result['gateway_response'] ?? 'The gateway declined the charge.');
                    return false;
                }

                $attempt->update(['status' => 'pending']);
                $subscription->update(['status' => 'past_due', 'retry_at' => null]);
                $transaction->update(['metadata' => ['subscription_id' => $subscription->id, 'billing_attempt_id' => $attempt->id, 'gateway_response' => $result]]);
                return false;
            } catch (Throwable $error) {
                report($error);
                // A timeout can happen after the provider accepted the charge. Keep this
                // attempt pending for webhook/reconciliation instead of retrying and
                // risking a duplicate customer charge.
                $attempt->update(['failure_reason' => Str::limit($error->getMessage(), 1000)]);
                $subscription->update(['status' => 'past_due', 'retry_at' => null]);
                $attempt->transaction?->update(['metadata' => array_merge($attempt->transaction->metadata ?? [], ['gateway_error' => Str::limit($error->getMessage(), 1000)])]);
                return false;
            }
        } finally {
            $lock->release();
        }
    }

    public function settleSuccess(BillingAttempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {
            $attempt = BillingAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($attempt->status === 'success') return;

            $subscription = BillingSubscription::whereKey($attempt->billing_subscription_id)->lockForUpdate()->firstOrFail();
            $now = now();
            $attempt->update(['status' => 'success', 'failure_reason' => null, 'retry_at' => null]);
            $attempt->transaction?->update(['status' => 'success']);
            $subscription->update([
                'status' => $subscription->interval_unit === 'once' ? 'completed' : 'active',
                'last_billed_at' => $now,
                'next_billing_at' => $subscription->interval_unit === 'once' ? null : $this->nextDate($now, $subscription->interval_unit, $subscription->interval_count),
                'retry_at' => null,
                'failed_attempts' => 0,
            ]);
        });
    }

    public function settleFailure(BillingAttempt $attempt, string $reason): void
    {
        DB::transaction(function () use ($attempt, $reason) {
            $attempt = BillingAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if (in_array($attempt->status, ['failed', 'success'], true)) return;

            $subscription = BillingSubscription::whereKey($attempt->billing_subscription_id)->lockForUpdate()->firstOrFail();
            $failures = $subscription->failed_attempts + 1;
            $retryAt = $failures < 3 ? now()->addDays($failures) : null;
            $attempt->update(['status' => 'failed', 'failure_reason' => Str::limit($reason, 1000), 'retry_at' => $retryAt]);
            $attempt->transaction?->update(['status' => 'failed']);
            $subscription->update(['status' => 'past_due', 'failed_attempts' => $failures, 'retry_at' => $retryAt]);
        });
    }

    private function nextDate(Carbon $from, string $unit, int $count): Carbon
    {
        return match ($unit) {
            'day' => $from->copy()->addDays($count),
            'week' => $from->copy()->addWeeks($count),
            'month' => $from->copy()->addMonthsNoOverflow($count),
            'year' => $from->copy()->addYearsNoOverflow($count),
            default => $from->copy()->addMonthNoOverflow(),
        };
    }
}
