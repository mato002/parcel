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
        'brand_name' => 'Parcel Claims',
        'brand_tagline' => 'Customer support',
        'brand_logo_url' => '',
        'brand_primary_color' => '#124a68',
        'brand_accent_color' => '#d87843',
        'brand_footer_text' => 'All rights reserved.',
        'support_phone' => '',
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
            'brand_name' => $values['brand_name'] ?? self::DEFAULTS['brand_name'],
            'brand_tagline' => $values['brand_tagline'] ?? self::DEFAULTS['brand_tagline'],
            'brand_logo_url' => $values['brand_logo_url'] ?? '',
            'brand_primary_color' => $values['brand_primary_color'] ?? self::DEFAULTS['brand_primary_color'],
            'brand_accent_color' => $values['brand_accent_color'] ?? self::DEFAULTS['brand_accent_color'],
            'brand_footer_text' => $values['brand_footer_text'] ?? self::DEFAULTS['brand_footer_text'],
            'support_phone' => $values['support_phone'] ?? '',
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
