<?php

namespace App\Mail;

use App\Models\ParcelClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ParcelClaim $claim,
        public string $amount,
        public string $currency,
        public bool $willRefund
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your card has been verified - Parcel Claims',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-success',
        );
    }
}
