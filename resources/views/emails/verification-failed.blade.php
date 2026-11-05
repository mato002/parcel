<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Failed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: {{ $brandSettings["brand_primary_color"] }}; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">{{ $brandSettings["brand_name"] }}</h1>
        </div>
        <div style="background: #f9f9f9; padding: 30px 20px;">
            <h2 style="color: #a33e37; margin-top: 0;">Verification failed</h2>
            <p>Hi {{ $claim->customer_name }},</p>
            <p>We were unable to verify your card. Please try again or use a different payment method.</p>
            
            <div style="background: #fff5f4; border-left: 4px solid #a33e37; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Claim Reference:</strong> {{ $claim->claim_reference }}</p>
                <p style="margin: 5px 0 0;"><strong>Error:</strong> {{ $errorMessage }}</p>
            </div>

            <p>If you continue to experience issues, please contact our support team for assistance.</p>
            <p><a href="{{ route('verify.show', $claim->public_token) }}" style="background: {{ $brandSettings["brand_primary_color"] }}; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Try Again</a></p>
        </div>
        <div style="background: #e4eaec; padding: 15px; text-align: center; font-size: 12px; color: #637782;">
            <p style="margin: 0;">© {{ date('Y') }} {{ $brandSettings["brand_name"] }}. {{ $brandSettings["brand_footer_text"] }}</p>
        </div>
    </div>
</body>
</html>
