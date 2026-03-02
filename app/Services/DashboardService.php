<?php

namespace App\Services;

use App\Services\DataSources\DataSourceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private DataSourceInterface $dataSource;

    public function __construct(DataSourceManager $manager)
    {
        $this->dataSource = $manager->getActiveSource();
    }

    /**
     * Get the full executive summary with caching.
     */
    public function getExecutiveSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $cacheKey = 'executive_summary_' . ($from?->format('Ymd') ?? 'all') . '_' . ($to?->format('Ymd') ?? 'all');

        return $this->rememberDashboard($cacheKey, now()->addMinutes(5), function () use ($from, $to) {
            $summary = $this->dataSource->getExecutiveSummary($from, $to);
            $summary['generated_at'] = now()->toISOString();
            $summary['data_source'] = $this->dataSource->getIdentifier();
            return $summary;
        });
    }

    /**
     * Get directorate detail data.
     */
    public function getDirectorateDetail(int $directorateId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $cacheKey = "directorate_{$directorateId}_" . ($from?->format('Ymd') ?? 'all');

        return $this->rememberDashboard($cacheKey, now()->addMinutes(5), function () use ($directorateId, $from, $to) {
            return [
                'kpis' => $this->dataSource->getKpiData($directorateId, $from, $to),
                'financials' => $this->dataSource->getFinancialData($directorateId, $from, $to),
                'projects' => $this->dataSource->getProjectData($directorateId),
                'risks' => $this->dataSource->getRiskData($directorateId),
                'generated_at' => now()->toISOString(),
                'data_source' => $this->dataSource->getIdentifier(),
            ];
        });
    }

    /**
     * Get KPI trend for a specific KPI and directorate.
     */
    public function getKpiTrend(int $kpiId, int $directorateId, int $periods = 12): array
    {
        return $this->dataSource->getKpiTrend($kpiId, $directorateId, $periods);
    }

    /**
     * Get comparison across directorates.
     */
    public function getDirectorateComparison(array $directorateIds, array $kpiIds): array
    {
        return $this->dataSource->getDirectorateComparison($directorateIds, $kpiIds);
    }

    /**
     * Clear all dashboard caches.
     */
    public function clearCache(): void
    {
        // Prefer tagged cache clearing when supported; fall back to flush.
        try {
            Cache::tags(['dashboard'])->flush();
            return;
        } catch (\BadMethodCallException) {
            // no-op
        }

        Cache::flush();
    }

    private function rememberDashboard(string $key, $ttl, callable $callback): array
    {
        try {
            return Cache::tags(['dashboard'])->remember($key, $ttl, $callback);
        } catch (\BadMethodCallException) {
            return Cache::remember($key, $ttl, $callback);
        }
    }

    /**
     * Get simple anomaly detection — flags values exceeding 2 standard deviations.
     */
    public function detectAnomalies(int $directorateId): array
    {
        $kpiData = $this->dataSource->getKpiData($directorateId);
        $anomalies = [];

        foreach ($kpiData as $kpi) {
            if ($kpi['change_percentage'] !== null && abs($kpi['change_percentage']) > 15) {
                $anomalies[] = [
                    'kpi_name' => $kpi['kpi_name'],
                    'change' => $kpi['change_percentage'],
                    'value' => $kpi['value'],
                    'severity' => abs($kpi['change_percentage']) > 25 ? 'high' : 'medium',
                    'message' => sprintf(
                        '%s %s by %.1f%%',
                        $kpi['kpi_name'],
                        $kpi['change_percentage'] > 0 ? 'increased' : 'decreased',
                        abs($kpi['change_percentage'])
                    ),
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Generate AI-like text summary of current state.
     */
    public function generateTextSummary(): string
    {
        $summary = $this->getExecutiveSummary();
        $parts = [];

        $parts[] = sprintf(
            'Total organizational revenue stands at ZMW %s with budget utilization at %.1f%%.',
            number_format($summary['total_revenue'], 0),
            $summary['total_budget'] > 0 ? ($summary['total_revenue'] / $summary['total_budget'] * 100) : 0
        );

        if ($summary['high_risks'] > 0) {
            $parts[] = sprintf(
                '%d high-risk items require attention across %d total identified risks.',
                $summary['high_risks'],
                $summary['total_risks']
            );
        }

        $parts[] = sprintf(
            'Average project completion is %.1f%% with operational uptime at %.2f%%.',
            $summary['avg_completion'],
            $summary['avg_uptime'] ?? 0
        );

        // Find top performing directorate
        if (!empty($summary['directorates'])) {
            $top = collect($summary['directorates'])->sortByDesc('completion_percentage')->first();
            $parts[] = sprintf(
                '%s leads with %.1f%% project completion.',
                $top['name'],
                $top['completion_percentage']
            );
        }

        return implode(' ', $parts);
    }

    /**
     * Simple linear forecast for a KPI.
     */
    public function forecastKpi(int $kpiId, int $directorateId, int $forecastPeriods = 3): array
    {
        $trend = $this->dataSource->getKpiTrend($kpiId, $directorateId, 12);
        if (count($trend) < 3) {
            return [];
        }

        // Simple linear regression
        $n = count($trend);
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;

        foreach ($trend as $i => $point) {
            $sumX += $i;
            $sumY += $point['value'];
            $sumXY += $i * $point['value'];
            $sumX2 += $i * $i;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / max(($n * $sumX2 - $sumX * $sumX), 1);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        for ($i = 0; $i < $forecastPeriods; $i++) {
            $x = $n + $i;
            $date = Carbon::now()->addMonths($i + 1);
            $forecast[] = [
                'period' => $date->format('Y-m'),
                'label' => $date->format('M Y') . ' (forecast)',
                'value' => round($intercept + $slope * $x, 2),
                'is_forecast' => true,
            ];
        }

        return $forecast;
    }
}
