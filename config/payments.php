<?php

use App\Services\Gateways\PaystackGateway;

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'paystack'),
    'gateways' => [
        'paystack' => PaystackGateway::class,
    ],
    'defaults' => [
        'currency' => env('PAYMENT_CURRENCY', env('PAYSTACK_CURRENCY', 'KES')),
        'verification_currency' => env('VERIFICATION_CURRENCY', env('PAYSTACK_VERIFICATION_CURRENCY', 'USD')),
        'verification_amount' => (int) env('VERIFICATION_AMOUNT', env('PAYSTACK_VERIFICATION_AMOUNT', 100)),
        'refund_verification' => env('REFUND_VERIFICATION', env('PAYSTACK_REFUND_VERIFICATION', false)),
    ],
];
