<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: {{ $brandSettings["brand_primary_color"] }}; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">{{ $brandSettings["brand_name"] }}</h1>
        </div>
        <div style="background: #f9f9f9; padding: 30px 20px;">
            <h2 style="color: {{ $brandSettings["brand_accent_color"] }}; margin-top: 0;">Refund confirmation</h2>
            <p>Hi {{ $claim->customer_name }},</p>
            <p>A refund has been processed for your payment.</p>
            
            <div style="background: white; border-left: 4px solid {{ $brandSettings["brand_accent_color"] }}; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>{{ $paymentSettings['payment_reference_label'] }}:</strong> {{ $claim->claim_reference }}</p>
                <p style="margin: 5px 0 0;"><strong>Refund Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
            </div>

            <p>The refund has been initiated to your original payment method. Please allow 5-10 business days for the refund to appear in your account, depending on your bank's processing time.</p>

            <p>If you have any questions about this refund, please contact our support team.</p>
        </div>
        <div style="background: #e4eaec; padding: 15px; text-align: center; font-size: 12px; color: #637782;">
            <p style="margin: 0;">© {{ date('Y') }} {{ $brandSettings["brand_name"] }}. {{ $brandSettings["brand_footer_text"] }}</p>
        </div>
    </div>
</body>
</html>
