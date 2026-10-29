<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParcelClaim extends Model
{
    protected $fillable = ['customer_name', 'customer_email', 'claim_reference', 'public_token', 'status'];
    public function cards(): HasMany { return $this->hasMany(SavedCard::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    public function subscriptions(): HasMany { return $this->hasMany(BillingSubscription::class); }
}
