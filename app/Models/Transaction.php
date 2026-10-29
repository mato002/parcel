<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['parcel_claim_id', 'saved_card_id', 'gateway', 'gateway_reference', 'type', 'amount', 'currency', 'status', 'description', 'refund_reference', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function claim(): BelongsTo { return $this->belongsTo(ParcelClaim::class, 'parcel_claim_id'); }
    public function card(): BelongsTo { return $this->belongsTo(SavedCard::class, 'saved_card_id'); }
}
