<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $actionRequired ? 'Payment Verification Required' : ($success ? 'Payment Successful' : 'Payment Failed') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: {{ $brandSettings["brand_primary_color"] }}; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">{{ $brandSettings["brand_name"] }}</h1>
        </div>
        <div style="background: #f9f9f9; padding: 30px 20px;">
            @if($success)
                <h2 style="color: {{ $brandSettings["brand_accent_color"] }}; margin-top: 0;">Payment successful</h2>
                <p>Hi,</p>
                <p>Your subscription payment was processed successfully.</p>
                
                <div style="background: white; border-left: 4px solid {{ $brandSettings["brand_accent_color"] }}; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Subscription:</strong> {{ $subscription->product_name }}</p>
                    <p style="margin: 5px 0 0;"><strong>Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
                </div>
            @elseif($actionRequired)
                <h2 style="color:#9c6820; margin-top: 0;">Bank verification required</h2>
                <p>Hi,</p>
                <p>Your bank needs you to confirm this subscription charge. Use the secure Paystack page below to complete verification.</p>
                <div style="background: #fffaf0; border-left: 4px solid #9c6820; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Subscription:</strong> {{ $subscription->product_name }}</p>
                    <p style="margin: 5px 0 0;"><strong>Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
                </div>
                @if($actionUrl)<p><a href="{{ $actionUrl }}" style="background: {{ $brandSettings['brand_primary_color'] }}; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Complete bank verification</a></p>@endif
            @else
                <h2 style="color: #a33e37; margin-top: 0;">Payment failed</h2>
                <p>Hi,</p>
                <p>We were unable to process your subscription payment.</p>
                
                <div style="background: #fff5f4; border-left: 4px solid #a33e37; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Subscription:</strong> {{ $subscription->product_name }}</p>
                    <p style="margin: 5px 0 0;"><strong>Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
                    <p style="margin: 5px 0 0;"><strong>Error:</strong> {{ $errorMessage }}</p>
                </div>

                <p>Please update your payment information or contact support if the issue persists.</p>
            @endif

            <p>If you have any questions, please contact our support team.</p>
        </div>
        <div style="background: #e4eaec; padding: 15px; text-align: center; font-size: 12px; color: #637782;">
            <p style="margin: 0;">© {{ date('Y') }} {{ $brandSettings["brand_name"] }}. {{ $brandSettings["brand_footer_text"] }}</p>
        </div>
    </div>
</body>
</html>
