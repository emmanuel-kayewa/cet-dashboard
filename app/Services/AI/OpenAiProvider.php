<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('dashboard.ai.openai.key', '');
        $this->model = config('dashboard.ai.openai.model', 'gpt-4');
        $this->baseUrl = config('dashboard.ai.openai.url', 'https://api.openai.com/v1');
        $this->timeout = config('dashboard.ai.openai.timeout', 60);
    }

    public function chat(string $systemPrompt, string $userPrompt, array $options = []): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 2048,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', '');
            }

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return '';
        } catch (\Exception $e) {
            Log::error('OpenAI provider error', ['error' => $e->getMessage()]);
            return '';
        }
    }

    public function chatWithJson(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $options['model'] ?? $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt . "\n\nYou MUST respond with valid JSON only."],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => $options['temperature'] ?? 0.3,
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '{}');
                $decoded = json_decode($content, true);
                return is_array($decoded) ? $decoded : [];
            }

            Log::error('OpenAI JSON API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('OpenAI JSON provider error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                ->get("{$this->baseUrl}/models");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    public function getIdentifier(): string
    {
        return "openai:{$this->model}";
    }
}
