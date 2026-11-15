<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Services\PaymentSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManualChargeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public bool $success,
        public ?string $checkoutUrl = null,
        public ?string $gatewayMessage = null,
        public bool $actionRequired = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->success ? 'Payment received - ' : 'Payment needs attention - ')
                .app(PaymentSettings::class)->all()['brand_name'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.manual-charge');
    }
}
