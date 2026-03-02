<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Send a chat completion request and return the text response.
     *
     * @param  string  $systemPrompt  The system/instruction prompt.
     * @param  string  $userPrompt    The user message / data payload.
     * @param  array   $options       Extra options (temperature, max_tokens, etc.)
     * @return string  The AI-generated text response.
     */
    public function chat(string $systemPrompt, string $userPrompt, array $options = []): string;

    /**
     * Send a chat request expecting a JSON-structured response.
     *
     * @param  string  $systemPrompt
     * @param  string  $userPrompt
     * @param  array   $options
     * @return array   Decoded JSON response.
     */
    public function chatWithJson(string $systemPrompt, string $userPrompt, array $options = []): array;

    /**
     * Check whether the AI provider is available and reachable.
     */
    public function isAvailable(): bool;

    /**
     * Get a human-readable identifier for this provider.
     */
    public function getIdentifier(): string;
}
