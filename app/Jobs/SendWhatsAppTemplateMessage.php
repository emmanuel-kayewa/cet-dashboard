<?php

namespace App\Jobs;

use App\Services\WhatsAppCloudService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppTemplateMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $to,
        public readonly string $templateName,
        public readonly array $bodyParameters = [],
        public readonly string $languageCode = 'en_US',
        public readonly ?string $buttonUrlParameter = null,
        public readonly int $buttonIndex = 0,
        public readonly array $context = [],
    ) {
    }

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(WhatsAppCloudService $whatsApp): void
    {
        $whatsApp->sendTemplateMessage(
            to: $this->to,
            templateName: $this->templateName,
            bodyParameters: $this->bodyParameters,
            languageCode: $this->languageCode,
            buttonUrlParameter: $this->buttonUrlParameter,
            buttonIndex: $this->buttonIndex,
        );

        Log::info('WhatsApp message sent', $this->context);
    }

    public function failed(Throwable $e): void
    {
        Log::error('WhatsApp message failed', array_merge($this->context, [
            'error' => $e->getMessage(),
        ]));
    }
}
