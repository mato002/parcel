<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingProduct extends Model
{
    protected $fillable = ['name', 'description', 'amount', 'currency', 'interval_unit', 'interval_count', 'active'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'interval_count' => 'integer', 'active' => 'boolean'];
    }

    public function subscriptions(): HasMany { return $this->hasMany(BillingSubscription::class); }

    public function intervalLabel(): string
    {
        if ($this->interval_unit === 'once') return 'One time';
        return 'Every '.($this->interval_count > 1 ? $this->interval_count.' ' : '').rtrim($this->interval_unit, 's').($this->interval_count > 1 ? 's' : '');
    }
}
