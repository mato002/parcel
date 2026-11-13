<?php

namespace App\Mail;

use App\Models\ParcelClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Services\PaymentSettings;

class VerificationFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ParcelClaim $claim,
        public string $errorMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment could not be completed - '.app(PaymentSettings::class)->all()['brand_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-failed',
        );
    }
}
