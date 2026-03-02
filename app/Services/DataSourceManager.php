<?php

namespace App\Services;

use App\Services\DataSources\DataSourceInterface;
use App\Services\DataSources\ManualInputDataSource;
use App\Services\DataSources\OracleDataSource;
use App\Services\DataSources\SimulationDataSource;
use InvalidArgumentException;

class DataSourceManager
{
    private array $sources = [];
    private ?DataSourceInterface $activeSource = null;

    public function __construct()
    {
        $this->registerDefaultSources();
    }

    /**
     * Get the active data source based on configuration.
     */
    public function getActiveSource(): DataSourceInterface
    {
        if ($this->activeSource) {
            return $this->activeSource;
        }

        $sourceKey = config('dashboard.data_source', 'simulation');
        $this->activeSource = $this->getSource($sourceKey);

        return $this->activeSource;
    }

    /**
     * Get a specific data source by key.
     */
    public function getSource(string $key): DataSourceInterface
    {
        if (!isset($this->sources[$key])) {
            throw new InvalidArgumentException("Data source '{$key}' is not registered.");
        }

        $source = $this->sources[$key];

        // Resolve from container if it's a class name string
        if (is_string($source)) {
            $source = app($source);
            $this->sources[$key] = $source;
        }

        return $source;
    }

    /**
     * Register a new data source.
     */
    public function register(string $key, DataSourceInterface|string $source): void
    {
        $this->sources[$key] = $source;
    }

    /**
     * Get all registered source keys.
     */
    public function getRegisteredSources(): array
    {
        return array_keys($this->sources);
    }

    /**
     * Switch the active data source at runtime.
     */
    public function switchTo(string $key): DataSourceInterface
    {
        $this->activeSource = $this->getSource($key);
        return $this->activeSource;
    }

    /**
     * Check which sources are available.
     */
    public function getAvailableSources(): array
    {
        $available = [];
        foreach ($this->sources as $key => $source) {
            $instance = is_string($source) ? app($source) : $source;
            $available[$key] = $instance->isAvailable();
        }
        return $available;
    }

    private function registerDefaultSources(): void
    {
        $this->sources = [
            'simulation' => SimulationDataSource::class,
            'manual' => ManualInputDataSource::class,
            'oracle' => OracleDataSource::class,
        ];
    }
}
