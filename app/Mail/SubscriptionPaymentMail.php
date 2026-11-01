<?php

namespace App\Mail;

use App\Models\BillingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public BillingSubscription $subscription,
        public string $amount,
        public string $currency,
        public bool $success,
        public ?string $errorMessage = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->success ? 'Payment successful - Parcel Claims' : 'Payment failed - Parcel Claims',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-payment',
        );
    }
}
