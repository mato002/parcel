<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Verified Successfully</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #14536b; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">Parcel Claims</h1>
        </div>
        <div style="background: #f9f9f9; padding: 30px 20px;">
            <h2 style="color: #14536b; margin-top: 0;">Your card has been verified</h2>
            <p>Hi {{ $claim->customer_name }},</p>
            <p>Great news! Your card verification was successful. Your claim can now proceed.</p>
            
            <div style="background: white; border-left: 4px solid #2c826d; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Claim Reference:</strong> {{ $claim->claim_reference }}</p>
                <p style="margin: 5px 0 0;"><strong>Verification Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
            </div>

            @if($willRefund)
                <p><strong>Refund Information:</strong> This verification charge will be automatically refunded to your card. The time it takes to appear depends on your bank.</p>
            @else
                <p><strong>Note:</strong> This is a real payment to verify your card. It is not automatically refunded.</p>
            @endif

            <p>If you have any questions about your claim, please contact our support team.</p>
        </div>
        <div style="background: #e4eaec; padding: 15px; text-align: center; font-size: 12px; color: #637782;">
            <p style="margin: 0;">© {{ date('Y') }} Parcel Claims. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
