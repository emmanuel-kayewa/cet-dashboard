<?php

namespace App\Services;

use App\Events\SimulationDataUpdated;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\KpiEntry;
use App\Models\SimulationLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SimulationService
{
    private int $seed;

    public function __construct()
    {
        $this->seed = config('dashboard.simulation.seed_value', 42);
    }

    /**
     * Check if simulation mode is active.
     */
    public function isEnabled(): bool
    {
        return Setting::getValue('simulation_enabled',
            config('dashboard.simulation.enabled', true)
        );
    }

    /**
     * Toggle simulation mode on/off.
     */
    public function toggle(bool $enabled): void
    {
        Setting::setValue('simulation_enabled', $enabled, 'boolean');
    }

    /**
     * Run a simulation cycle — generates new data points for all directorates.
     */
    public function runSimulationCycle(): array
    {
        if (!$this->isEnabled()) {
            return ['status' => 'disabled', 'message' => 'Simulation mode is currently off.'];
        }

        $directorates = Directorate::active()->with(['kpis' => fn($q) => $q->where('is_active', true)])->get();
        $kpis = Kpi::active()->get();
        $generated = [];

        foreach ($directorates as $directorate) {
            foreach ($directorate->kpis as $kpi) {
                $entry = $this->generateKpiEntry($kpi, $directorate);
                $generated[] = $entry;
            }
        }

        // Log the simulation cycle
        SimulationLog::create([
            'event_type' => 'simulation_cycle',
            'data' => [
                'entries_generated' => count($generated),
                'timestamp' => now()->toISOString(),
            ],
            'status' => 'applied',
        ]);

        // Invalidate dashboard cache so the UI reflects new entries immediately.
        try {
            Cache::tags(['dashboard'])->flush();
        } catch (\BadMethodCallException) {
            Cache::flush();
        }

        // Broadcast the update via WebSocket
        try {
            broadcast(new SimulationDataUpdated($generated))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast simulation update: ' . $e->getMessage());
        }

        return [
            'status' => 'success',
            'entries_generated' => count($generated),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Generate a single KPI entry for simulation.
     */
    private function generateKpiEntry(Kpi $kpi, Directorate $directorate): KpiEntry
    {
        // Ensure the KPI is linked to the directorate.
        $directorate->kpis()->syncWithoutDetaching([$kpi->id]);

        $dateSeed = $this->seed + $kpi->id + $directorate->id + (int) now()->format('YmdH');
        mt_srand($dateSeed);

        // Get the previous entry to create realistic trends
        $previous = KpiEntry::where('kpi_id', $kpi->id)
            ->where('directorate_id', $directorate->id)
            ->orderByDesc('period_date')
            ->first();

        $previousValue = $previous ? $previous->value : $this->getBaselineValue($kpi);

        // Apply trend with noise
        $changePercent = (mt_rand(-500, 700) / 10000); // -5% to +7% change
        $newValue = round($previousValue * (1 + $changePercent), 2);

        // Clamp percentage values
        if ($kpi->unit === 'percentage') {
            $newValue = max(0, min(100, $newValue));
        }

        mt_srand();

        return KpiEntry::create([
            'kpi_id' => $kpi->id,
            'directorate_id' => $directorate->id,
            'value' => $newValue,
            'previous_value' => $previousValue,
            'period_date' => now()->startOfDay(),
            'period_type' => 'daily',
            'source' => 'simulation',
        ]);
    }

    /**
     * Get a reasonable baseline value for initial simulation.
     */
    private function getBaselineValue(Kpi $kpi): float
    {
        return match ($kpi->category) {
            'financial' => mt_rand(10000000, 50000000),
            'operational' => mt_rand(85, 99) + mt_rand(0, 99) / 100,
            'strategic' => mt_rand(50, 90),
            'risk' => mt_rand(3, 15),
            'hr' => mt_rand(100, 3000),
            'customer' => mt_rand(50000, 2000000),
            'project' => mt_rand(20, 95),
            'technical' => mt_rand(92, 100) + mt_rand(0, 99) / 100,
            default => mt_rand(50, 100),
        };
    }

    /**
     * Generate historical data for initial seeding.
     */
    public function generateHistoricalData(int $months = 12): int
    {
        $directorates = Directorate::active()->with(['kpis' => fn($q) => $q->where('is_active', true)])->get();
        $count = 0;

        foreach ($directorates as $directorate) {
            foreach ($directorate->kpis as $kpi) {
                $baseValue = $this->getBaselineValue($kpi);

                for ($i = $months; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i)->startOfMonth();
                    mt_srand($this->seed + $kpi->id + $directorate->id + (int) $date->format('Ym'));

                    $trendFactor = 1 + (($months - $i) * 0.003);
                    $noise = (mt_rand(-300, 400) / 10000);
                    $value = round($baseValue * $trendFactor * (1 + $noise), 2);

                    if ($kpi->unit === 'percentage') {
                        $value = max(0, min(100, $value));
                    }

                    KpiEntry::updateOrCreate(
                        [
                            'kpi_id' => $kpi->id,
                            'directorate_id' => $directorate->id,
                            'period_date' => $date,
                            'period_type' => 'monthly',
                            'source' => 'simulation',
                        ],
                        [
                            'value' => $value,
                            'previous_value' => $baseValue * (1 + (($months - $i - 1) * 0.003)),
                        ]
                    );

                    $count++;
                }
            }
        }

        mt_srand();
        return $count;
    }
}
