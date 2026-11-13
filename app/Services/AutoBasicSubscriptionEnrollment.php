<?php

namespace App\Services;

use App\Models\BillingProduct;
use App\Models\BillingSubscription;
use App\Models\ParcelClaim;
use App\Models\SavedCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AutoBasicSubscriptionEnrollment
{
    public function enroll(ParcelClaim $claim): ?BillingSubscription
    {
        $product = BillingProduct::query()
            ->whereRaw('LOWER(name) = ?', ['basic'])
            ->where('active', true)
            ->whereIn('interval_unit', ['day', 'week', 'month', 'year'])
            ->orderByDesc('id')
            ->first();

        if (! $product) {
            return null;
        }

        $card = SavedCard::query()
            ->where('parcel_claim_id', $claim->id)
            ->where('reusable', true)
            ->latest('id')
            ->first();

        if (! $card) {
            return null;
        }

        return DB::transaction(function () use ($claim, $card, $product) {
            ParcelClaim::query()->whereKey($claim->id)->lockForUpdate()->firstOrFail();

            $subscription = BillingSubscription::query()
                ->where('parcel_claim_id', $claim->id)
                ->where('billing_product_id', $product->id)
                ->whereIn('status', ['active', 'past_due', 'paused'])
                ->lockForUpdate()
                ->first();

            if ($subscription) {
                if ($subscription->saved_card_id !== $card->id) {
                    $subscription->update(['saved_card_id' => $card->id]);
                }

                return $subscription->refresh();
            }

            $startsAt = now();
            $nextBillingAt = $this->nextBillingDate($startsAt, $product->interval_unit, $product->interval_count);

            return BillingSubscription::create([
                'parcel_claim_id' => $claim->id,
                'saved_card_id' => $card->id,
                'billing_product_id' => $product->id,
                'product_name' => $product->name,
                'amount' => $product->amount,
                'currency' => $product->currency,
                'interval_unit' => $product->interval_unit,
                'interval_count' => $product->interval_count,
                'status' => 'active',
                'starts_at' => $startsAt,
                'next_billing_at' => $nextBillingAt,
                'consented_at' => $startsAt,
                'consent_record' => 'The account administrator confirmed the customer agreed to the Basic recurring plan by phone before enrollment. The original call date and time were not supplied; this timestamp records automated enrollment, not the phone call. Enrollment was triggered by a successful payment or saved reusable card, and the first charge is scheduled for the next plan interval.',
            ]);
        });
    }

    private function nextBillingDate(Carbon $from, string $unit, int $count): Carbon
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
