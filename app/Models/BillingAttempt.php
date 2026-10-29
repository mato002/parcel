<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingAttempt extends Model
{
    protected $fillable = [
        'billing_subscription_id', 'transaction_id', 'scheduled_for', 'dedupe_key',
        'attempt_number', 'status', 'failure_reason', 'retry_at',
    ];

    protected function casts(): array
    {
        return ['scheduled_for' => 'datetime', 'retry_at' => 'datetime', 'attempt_number' => 'integer'];
    }

    public function subscription(): BelongsTo { return $this->belongsTo(BillingSubscription::class, 'billing_subscription_id'); }
    public function transaction(): BelongsTo { return $this->belongsTo(Transaction::class); }
}
