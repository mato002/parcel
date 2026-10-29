<?php

namespace App\Console\Commands;

use App\Models\BillingSubscription;
use App\Services\SubscriptionBillingService;
use Illuminate\Console\Command;

class BillDueSubscriptions extends Command
{
    protected $signature = 'billing:charge-due {--limit=100 : Maximum subscriptions to process per run}';
    protected $description = 'Charge due customer subscriptions using their saved reusable payment methods';

    public function handle(SubscriptionBillingService $billing): int
    {
        $now = now();
        $subscriptions = BillingSubscription::query()
            ->where(function ($query) use ($now) {
                $query->where(fn ($active) => $active->where('status', 'active')->whereNotNull('next_billing_at')->where('next_billing_at', '<=', $now))
                    ->orWhere(fn ($retry) => $retry->where('status', 'past_due')->whereNotNull('retry_at')->where('retry_at', '<=', $now));
            })
            ->orderByRaw('COALESCE(retry_at, next_billing_at) asc')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        $processed = 0;
        foreach ($subscriptions as $subscription) {
            $billing->bill($subscription, scheduled: true);
            $processed++;
        }

        $this->info("Processed {$processed} due subscription(s).");
        return self::SUCCESS;
    }
}
