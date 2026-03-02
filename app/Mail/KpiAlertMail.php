<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KpiAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alert $alert,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->alert->severity) {
            'critical' => '🔴 CRITICAL',
            'warning' => '🟡 WARNING',
            default => 'ℹ️ INFO',
        };

        return new Envelope(
            subject: "{$prefix}: {$this->alert->title} — ZESCO Dashboard",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kpi-alert',
            with: [
                'alert' => $this->alert,
                'user' => $this->recipient,
                'dashboardUrl' => url('/dashboard'),
            ],
        );
    }
}
