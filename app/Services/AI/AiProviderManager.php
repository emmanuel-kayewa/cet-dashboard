<?php

namespace App\Services\AI;

use InvalidArgumentException;

class AiProviderManager
{
    private ?AiProviderInterface $provider = null;

    /**
     * Get the active AI provider instance.
     */
    public function provider(): AiProviderInterface
    {
        if ($this->provider === null) {
            $this->provider = $this->resolveProvider();
        }

        return $this->provider;
    }

    /**
     * Check if any AI provider is configured and available.
     */
    public function isAvailable(): bool
    {
        if (!config('dashboard.ai.enabled', true)) {
            return false;
        }

        return $this->provider()->isAvailable();
    }

    /**
     * Get the identifier of the active provider.
     */
    public function getIdentifier(): string
    {
        return $this->provider()->getIdentifier();
    }

    /**
     * Resolve the provider from config.
     */
    private function resolveProvider(): AiProviderInterface
    {
        $driver = config('dashboard.ai.provider', 'ollama');

        return match ($driver) {
            'ollama' => new OllamaProvider(),
            'openai' => new OpenAiProvider(),
            default => throw new InvalidArgumentException("Unknown AI provider: {$driver}"),
        };
    }
}
