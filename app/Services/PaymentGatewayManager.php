<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Illuminate\Support\Str;

class PaymentGatewayManager
{
    /** @return array<string, string> */
    public function available(): array
    {
        $available = [];
        foreach (config('payments.gateways', []) as $key => $class) {
            $gateway = App::make($class);
            if ($gateway instanceof PaymentGateway) $available[$key] = $gateway->name();
        }
        return $available;
    }

    public function current(): PaymentGateway
    {
        return $this->for(app(PaymentSettings::class)->all()['gateway']);
    }

    public function for(?string $key = null): PaymentGateway
    {
        $key ??= config('payments.default_gateway');
        $class = config("payments.gateways.{$key}");
        if (! $class) throw new InvalidArgumentException("Payment gateway [{$key}] is not installed.");
        $gateway = App::make($class);
        if (! $gateway instanceof PaymentGateway) throw new InvalidArgumentException("Payment gateway [{$key}] has an invalid adapter.");
        return $gateway;
    }

    public function name(?string $key = null): string
    {
        $key ??= config('payments.default_gateway');
        if (! config("payments.gateways.{$key}")) return Str::headline($key).' (adapter unavailable)';
        return $this->for($key)->name();
    }
}
