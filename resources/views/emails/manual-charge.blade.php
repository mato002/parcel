<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $success ? 'Payment received' : 'Payment needs attention' }}</title></head>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#263640;margin:0;padding:0">
    <div style="max-width:600px;margin:0 auto;padding:20px">
        <div style="background:{{ $brandSettings['brand_primary_color'] }};padding:20px;text-align:center"><h1 style="color:#fff;margin:0">{{ $brandSettings['brand_name'] }}</h1></div>
        <div style="background:#f8fafb;padding:30px 20px">
            @if($success)
                <h2 style="color:{{ $brandSettings['brand_accent_color'] }};margin-top:0">Payment received</h2>
                <p>Hi {{ $transaction->claim?->customer_name ?? 'there' }},</p>
                <p>Your payment was processed successfully.</p>
            @elseif($actionRequired)
                <h2 style="color:#9c6820;margin-top:0">Bank verification required</h2>
                <p>Hi {{ $transaction->claim?->customer_name ?? 'there' }},</p>
                <p>Your bank needs you to confirm this card charge. Use the secure Paystack page below to complete its verification.</p>
            @else
                <h2 style="color:#a3443d;margin-top:0">Payment needs attention</h2>
                <p>Hi {{ $transaction->claim?->customer_name ?? 'there' }},</p>
                <p>We couldn't complete the saved-card charge. You can try checkout securely with a card and complete any bank verification required.</p>
            @endif
            <div style="background:#fff;padding:15px;border-left:4px solid {{ $success ? $brandSettings['brand_accent_color'] : ($actionRequired ? '#9c6820' : '#a3443d') }};margin:20px 0">
                <p style="margin:0"><strong>{{ $paymentSettings['payment_reference_label'] }}:</strong> {{ $transaction->claim?->claim_reference ?? '—' }}</p>
                <p style="margin:5px 0 0"><strong>Amount:</strong> {{ number_format($transaction->amount / 100, 2) }} {{ $transaction->currency }}</p>
                <p style="margin:5px 0 0"><strong>Description:</strong> {{ $transaction->description }}</p>
                @if(!$success && $gatewayMessage)<p style="margin:5px 0 0"><strong>Gateway message:</strong> {{ $gatewayMessage }}</p>@endif
            </div>
            @if(!$success && $checkoutUrl)
                <p><a href="{{ $checkoutUrl }}" style="display:inline-block;background:{{ $brandSettings['brand_primary_color'] }};color:#fff;padding:12px 20px;text-decoration:none;border-radius:5px">{{ $actionRequired ? 'Complete bank verification' : 'Continue to secure checkout' }}</a></p>
                @unless($actionRequired)<p style="font-size:13px;color:#647680">If your bank declines the card, please contact them or use another payment method.</p>@endunless
            @endif
            @if($paymentSettings['support_email'])<p>Questions? Contact <a href="mailto:{{ $paymentSettings['support_email'] }}">{{ $paymentSettings['support_email'] }}</a>.</p>@elseif($paymentSettings['support_phone'])<p>Questions? Contact {{ $paymentSettings['support_phone'] }}.</p>@endif
        </div>
        <div style="background:#e9eff1;padding:15px;text-align:center;font-size:12px;color:#637782">© {{ date('Y') }} {{ $brandSettings['brand_name'] }}. {{ $brandSettings['brand_footer_text'] }}</div>
    </div>
</body>
</html>
