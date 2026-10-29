<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class PaymentSettings
{
    private const DEFAULTS = [
        'gateway' => 'paystack',
        'currency' => 'KES',
        'verification_currency' => 'USD',
        'verification_amount' => '100',
        'refund_verification' => '0',
        'support_email' => '',
    ];

    public function all(): array
    {
        $values = SystemSetting::query()->pluck('value', 'key')->all();

        return [
            'gateway' => $values['gateway'] ?? config('payments.default_gateway', self::DEFAULTS['gateway']),
            'gateway_name' => app(PaymentGatewayManager::class)->name($values['gateway'] ?? config('payments.default_gateway', self::DEFAULTS['gateway'])),
            'currency' => $values['currency'] ?? config('payments.defaults.currency', self::DEFAULTS['currency']),
            'verification_currency' => $values['verification_currency'] ?? config('payments.defaults.verification_currency', self::DEFAULTS['verification_currency']),
            'verification_amount' => (int) ($values['verification_amount'] ?? config('payments.defaults.verification_amount', self::DEFAULTS['verification_amount'])),
            'refund_verification' => filter_var($values['refund_verification'] ?? config('payments.defaults.refund_verification', self::DEFAULTS['refund_verification']), FILTER_VALIDATE_BOOL),
            'support_email' => $values['support_email'] ?? '',
        ];
    }

    public function save(array $settings): void
    {
        DB::transaction(function () use ($settings): void {
            foreach ($settings as $key => $value) {
                SystemSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
            }
        });
    }
}
