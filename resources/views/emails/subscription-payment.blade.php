<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Payment Successful' : 'Payment Failed' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #14536b; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">Parcel Claims</h1>
        </div>
        <div style="background: #f9f9f9; padding: 30px 20px;">
            @if($success)
                <h2 style="color: #2c826d; margin-top: 0;">Payment successful</h2>
                <p>Hi,</p>
                <p>Your subscription payment was processed successfully.</p>
                
                <div style="background: white; border-left: 4px solid #2c826d; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Subscription:</strong> {{ $subscription->product_name }}</p>
                    <p style="margin: 5px 0 0;"><strong>Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
                </div>
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
            <p style="margin: 0;">© {{ date('Y') }} Parcel Claims. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
