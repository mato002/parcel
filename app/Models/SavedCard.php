<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedCard extends Model
{
    protected $fillable = ['parcel_claim_id', 'gateway', 'authorization_code', 'authorization_hash', 'card_type', 'last4', 'exp_month', 'exp_year', 'customer_email', 'gateway_customer_code', 'reusable'];
    protected $hidden = ['authorization_code'];
    protected function casts(): array { return ['authorization_code' => 'encrypted', 'reusable' => 'boolean']; }
    public function claim(): BelongsTo { return $this->belongsTo(ParcelClaim::class, 'parcel_claim_id'); }
    public function subscriptions(): HasMany { return $this->hasMany(BillingSubscription::class, 'saved_card_id'); }
}
