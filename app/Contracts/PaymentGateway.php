<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function key(): string;
    public function name(): string;
    public function configured(): bool;
    public function initialize(array $payload): array;
    public function callbackReference(Request $request): ?string;
    public function verify(string $reference): array;
    public function chargeAuthorization(string $authorizationCode, string $email, int $amount, string $reference, array $metadata = [], ?string $currency = null): array;
    public function refund(string $reference, ?int $amount = null, ?string $reason = null): array;
    /** Return a normalized charge event, or null when the webhook is unrelated. */
    public function chargeEventFromWebhook(Request $request): ?array;
}
