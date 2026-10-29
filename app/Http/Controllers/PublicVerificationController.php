<?php

namespace App\Http\Controllers;

use App\Models\ParcelClaim;
use App\Models\Transaction;
use App\Services\GatewayReference;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentSettings;
use Illuminate\Http\Request;

class PublicVerificationController extends Controller
{
    public function show(string $token, PaymentSettings $settings)
    {
        $claim = ParcelClaim::where('public_token', $token)->firstOrFail();
        return view('verify', ['claim' => $claim, 'paymentSettings' => $settings->all(), 'gatewayName' => app(PaymentGatewayManager::class)->current()->name()]);
    }

    public function initialize(Request $request, ParcelClaim $claim, PaymentGatewayManager $gateways, PaymentSettings $settings)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255']]);
        $claim->update(['customer_name' => $data['name'], 'customer_email' => $data['email']]);
        $gateway = $gateways->current();
        $reference = GatewayReference::make();
        $result = $gateway->initialize([
            'email' => $data['email'], 'amount' => $settings->all()['verification_amount'],
            'currency' => $settings->all()['verification_currency'], 'reference' => $reference,
            'channels' => ['card'],
            'callback_url' => route('verify.complete', ['token' => $claim->public_token, 'gateway' => $gateway->key()]),
            'metadata' => ['public_token' => $claim->public_token, 'claim_reference' => $claim->claim_reference,
                'customer_name' => $data['name'], 'custom_fields' => []],
        ]);
        Transaction::create(['parcel_claim_id' => $claim->id, 'gateway' => $gateway->key(), 'gateway_reference' => $reference,
            'type' => 'verification', 'amount' => $settings->all()['verification_amount'],
            'currency' => $settings->all()['verification_currency'], 'status' => 'pending', 'description' => 'Card verification',
            'metadata' => ['public_token' => $claim->public_token]]);
        return response()->json([
            'access_code' => $result['access_code'] ?? null,
            'redirect_url' => $result['authorization_url'] ?? null,
            'reference' => $reference,
        ]);
    }

    public function complete(Request $request, string $token, PaymentGatewayManager $gateways)
    {
        $claim = ParcelClaim::where('public_token', $token)->firstOrFail();
        $gateway = $gateways->for((string) $request->query('gateway', app(PaymentSettings::class)->all()['gateway']));
        $reference = $gateway->callbackReference($request);
        if (! $reference) return redirect()->route('verify.show', $token)->with('error', 'Payment was not completed.');
        $transaction = Transaction::where('gateway_reference', $reference)->where('parcel_claim_id', $claim->id)->first();
        if (! $transaction) return redirect()->route('verify.show', $token)->with('error', 'We could not find this payment attempt. Please contact support.');
        if ($transaction->gateway !== $gateway->key()) return redirect()->route('verify.show', $token)->with('error', 'The payment provider does not match this payment attempt.');
        $data = $gateway->verify($reference);
        if (($data['status'] ?? null) !== 'success' || ($data['metadata']['public_token'] ?? null) !== $claim->public_token) {
            return redirect()->route('verify.show', $token)->with('error', 'We could not confirm the verification charge. Please contact support.');
        }
        return view('verify-success', compact('claim'));
    }

    public function status(string $token, string $reference, PaymentGatewayManager $gateways)
    {
        $claim = ParcelClaim::where('public_token', $token)->firstOrFail();
        $transaction = Transaction::where('gateway_reference', $reference)
            ->where('parcel_claim_id', $claim->id)
            ->where('type', 'verification')
            ->firstOrFail();
        $gateway = $gateways->for($transaction->gateway);
        $data = $gateway->verify($reference);
        $valid = ($data['status'] ?? null) === 'success'
            && ($data['metadata']['public_token'] ?? null) === $claim->public_token
            && (int) ($data['amount'] ?? 0) === $transaction->amount
            && ($data['currency'] ?? null) === $transaction->currency;

        return response()->json(['status' => $valid ? 'success' : ($data['status'] ?? 'pending')]);
    }
}
