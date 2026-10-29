<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGateway;
use App\Services\PaymentSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    public function __construct(private PaymentSettings $settings) {}

    public function key(): string { return 'paystack'; }
    public function name(): string { return 'Paystack'; }
    public function configured(): bool { return (bool) config('services.paystack.secret_key'); }

    private function client(): PendingRequest
    {
        $secret = config('services.paystack.secret_key');
        if (! $secret) throw new RuntimeException($this->name().' credentials are not configured.');
        return Http::baseUrl('https://api.paystack.co')->withToken($secret)->acceptJson()->asJson()->timeout(20);
    }

    public function chargeAuthorization(string $authorizationCode, string $email, int $amount, string $reference, array $metadata = [], ?string $currency = null): array
    {
        return $this->request('post', '/transaction/charge_authorization', [
            'authorization_code' => $authorizationCode, 'email' => $email,
            'amount' => $amount, 'currency' => $currency ?? $this->settings->all()['currency'],
            'reference' => $reference, 'metadata' => $metadata,
        ]);
    }

    public function refund(string $reference, ?int $amount = null, ?string $reason = null): array
    {
        return $this->request('post', '/refund', array_filter([
            'transaction' => $reference, 'amount' => $amount, 'merchant_note' => $reason,
        ], fn ($value) => $value !== null));
    }

    public function verify(string $reference): array { return $this->request('get', '/transaction/verify/'.urlencode($reference)); }
    public function initialize(array $payload): array { return $this->request('post', '/transaction/initialize', $payload); }
    public function callbackReference(Request $request): ?string { return $request->query('reference'); }

    public function chargeEventFromWebhook(Request $request): ?array
    {
        $secret = config('services.paystack.secret_key');
        $signature = $request->header('x-paystack-signature', '');
        if (! $secret || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            throw new RuntimeException('Invalid gateway webhook signature.');
        }

        $event = $request->json()->all();
        if (! in_array(($event['event'] ?? null), ['charge.success', 'charge.failed'], true)) return null;
        $data = $event['data'] ?? [];
        $authorization = $data['authorization'] ?? [];

        return [
            'reference' => $data['reference'] ?? null,
            'status' => $data['status'] ?? (($event['event'] ?? null) === 'charge.success' ? 'success' : 'failed'),
            'message' => $data['gateway_response'] ?? $data['message'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? null,
            'metadata' => $data['metadata'] ?? [],
            'authorization' => [
                'code' => $authorization['authorization_code'] ?? null,
                'card_type' => $authorization['card_type'] ?? null,
                'last4' => $authorization['last4'] ?? null,
                'exp_month' => $authorization['exp_month'] ?? null,
                'exp_year' => $authorization['exp_year'] ?? null,
                'reusable' => (bool) ($authorization['reusable'] ?? false),
            ],
            'customer' => [
                'email' => $data['customer']['email'] ?? null,
                'code' => $data['customer']['customer_code'] ?? null,
            ],
        ];
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $response = $this->client()->{$method}($path, $payload);
        if (! $response->successful() || ! $response->json('status')) {
            throw new RuntimeException($response->json('message') ?: $this->name().' request failed.');
        }
        return $response->json('data', []);
    }
}
