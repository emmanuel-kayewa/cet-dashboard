<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $digest,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $headline = $this->digest['headline'] ?? 'Weekly Performance Digest';

        return new Envelope(
            subject: "ZESCO Weekly Digest: {$headline}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
            with: [
                'digest' => $this->digest,
                'user' => $this->recipient,
                'dashboardUrl' => url('/dashboard'),
                'aiInsightsUrl' => url('/ai'),
            ],
        );
    }
}
