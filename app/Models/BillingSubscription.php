<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingSubscription extends Model
{
    protected $fillable = [
        'parcel_claim_id', 'saved_card_id', 'billing_product_id', 'product_name', 'amount', 'currency',
        'interval_unit', 'interval_count', 'status', 'starts_at', 'next_billing_at', 'last_billed_at',
        'retry_at', 'failed_attempts', 'consented_at', 'consent_record', 'consented_by', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer', 'interval_count' => 'integer', 'failed_attempts' => 'integer',
            'starts_at' => 'datetime', 'next_billing_at' => 'datetime', 'last_billed_at' => 'datetime',
            'retry_at' => 'datetime', 'consented_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    public function claim(): BelongsTo { return $this->belongsTo(ParcelClaim::class, 'parcel_claim_id'); }
    public function card(): BelongsTo { return $this->belongsTo(SavedCard::class, 'saved_card_id'); }
    public function product(): BelongsTo { return $this->belongsTo(BillingProduct::class, 'billing_product_id'); }
    public function consentedBy(): BelongsTo { return $this->belongsTo(User::class, 'consented_by'); }
    public function attempts(): HasMany { return $this->hasMany(BillingAttempt::class, 'billing_subscription_id'); }
}
