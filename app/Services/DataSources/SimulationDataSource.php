<?php

namespace App\Services\DataSources;

use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\KpiEntry;
use App\Models\SimulationLog;
use Carbon\Carbon;

class SimulationDataSource implements DataSourceInterface
{
    private array $baseValues = [];
    private int $seed;

    public function __construct()
    {
        $this->seed = config('dashboard.simulation.seed_value', 42);
        $this->initializeBaseValues();
    }

    public function getIdentifier(): string
    {
        return 'simulation';
    }

    public function isAvailable(): bool
    {
        return config('dashboard.simulation.enabled', true);
    }

    public function getKpiData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $directorate = Directorate::with('kpis')->find($directorateId);
        if (!$directorate) {
            return [];
        }

        $kpis = $directorate->kpis()->active()->get();
        $data = [];

        foreach ($kpis as $kpi) {
            // Prefer persisted simulation entries (created by SimulationService),
            // but fall back to manual entries if simulation data isn't present yet.
            $simulationQuery = KpiEntry::where('kpi_id', $kpi->id)
                ->where('directorate_id', $directorateId)
                ->where('source', 'simulation');

            $manualQuery = KpiEntry::where('kpi_id', $kpi->id)
                ->where('directorate_id', $directorateId)
                ->where('source', 'manual');

            if ($fromDate) {
                $simulationQuery->where('period_date', '>=', $fromDate);
                $manualQuery->where('period_date', '>=', $fromDate);
            }
            if ($toDate) {
                $simulationQuery->where('period_date', '<=', $toDate);
                $manualQuery->where('period_date', '<=', $toDate);
            }

            $latest = (clone $simulationQuery)->orderByDesc('period_date')->first();
            $previous = (clone $simulationQuery)->orderByDesc('period_date')->skip(1)->first();

            if (!$latest) {
                $latest = (clone $manualQuery)->orderByDesc('period_date')->first();
                $previous = (clone $manualQuery)->orderByDesc('period_date')->skip(1)->first();
            }

            // Final fallback: deterministic synthetic values if no DB data exists yet.
            $value = $latest ? (float) $latest->value : $this->generateKpiValue($kpi, $directorateId);
            $previousValue = $previous
                ? (float) $previous->value
                : ($latest ? (float) ($latest->previous_value ?? 0) : $this->generateKpiValue($kpi, $directorateId, -1));

            $changePercentage = null;
            if ($latest) {
                $changePercentage = $latest->getChangePercentage();
            } elseif ($previousValue > 0) {
                $changePercentage = round((($value - $previousValue) / $previousValue) * 100, 2);
            }

            $data[] = [
                'kpi_id' => $kpi->id,
                'kpi_name' => $kpi->name,
                'kpi_slug' => $kpi->slug,
                'category' => $kpi->category,
                'value' => $value,
                'previous_value' => $previousValue,
                'target' => $kpi->pivot->custom_target ?? $kpi->target_value,
                'unit' => $kpi->unit,
                'trend_direction' => $kpi->trend_direction,
                'change_percentage' => $changePercentage,
                'status' => $kpi->getStatusForValue($value),
                'formatted_value' => $kpi->formatValue($value),
            ];
        }

        return $data;
    }

    public function getFinancialData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $months = $this->getMonthRange($fromDate, $toDate);
        $data = [];

        foreach ($months as $month) {
            $monthSeed = $this->seed + $directorateId + crc32($month->format('Y-m'));

            $revenue = $this->simulateValue($monthSeed, 5000000, 50000000, 'gradual_increase');
            $budget = $this->simulateValue($monthSeed + 1, 4000000, 45000000, 'stable');
            $opex = $this->simulateValue($monthSeed + 2, 2000000, 20000000, 'slight_increase');
            $capex = $this->simulateValue($monthSeed + 3, 1000000, 15000000, 'variable');

            $data[] = [
                'period' => $month->format('Y-m'),
                'period_label' => $month->format('M Y'),
                'revenue' => round($revenue, 2),
                'budget' => round($budget, 2),
                'opex' => round($opex, 2),
                'capex' => round($capex, 2),
                'budget_utilization' => round(($opex + $capex) / max($budget, 1) * 100, 1),
                'net_position' => round($revenue - $opex - $capex, 2),
            ];
        }

        return $data;
    }

    public function getProjectData(int $directorateId): array
    {
        $statuses = ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $projects = [];

        $projectCount = ($directorateId % 5) + 3;

        for ($i = 1; $i <= $projectCount; $i++) {
            $pSeed = $this->seed + $directorateId * 100 + $i;
            mt_srand($pSeed);

            $budget = mt_rand(1000000, 100000000);
            $completion = mt_rand(0, 100);
            $statusIdx = $completion >= 100 ? 3 : ($completion > 0 ? 1 : 0);

            $startDate = Carbon::now()->subMonths(mt_rand(1, 24));
            $endDate = $startDate->copy()->addMonths(mt_rand(3, 18));

            $projects[] = [
                'id' => $i,
                'name' => "Project {$directorateId}-{$i}",
                'status' => $statuses[$statusIdx],
                'priority' => $priorities[mt_rand(0, 3)],
                'budget' => $budget,
                'spent' => round($budget * ($completion / 100) * (1 + (mt_rand(-10, 15) / 100)), 2),
                'completion_percentage' => $completion,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'is_overdue' => $endDate->isPast() && $completion < 100,
                'days_remaining' => max(0, now()->diffInDays($endDate, false)),
            ];
        }

        mt_srand(); // Reset seed
        return $projects;
    }

    public function getRiskData(int $directorateId): array
    {
        $categories = ['operational', 'financial', 'strategic', 'compliance', 'technical'];
        $risks = [];

        $riskCount = ($directorateId % 4) + 2;
        mt_srand($this->seed + $directorateId * 200);

        for ($i = 1; $i <= $riskCount; $i++) {
            $likelihood = mt_rand(1, 5);
            $impact = mt_rand(1, 5);
            $score = $likelihood * $impact;

            $risks[] = [
                'id' => $i,
                'title' => "Risk {$directorateId}-{$i}",
                'category' => $categories[mt_rand(0, count($categories) - 1)],
                'likelihood' => $likelihood,
                'impact' => $impact,
                'risk_score' => $score,
                'level' => match (true) {
                    $score >= 20 => 'critical',
                    $score >= 12 => 'high',
                    $score >= 6 => 'medium',
                    default => 'low',
                },
                'status' => ['identified', 'assessed', 'mitigating'][mt_rand(0, 2)],
            ];
        }

        mt_srand();
        return $risks;
    }

    public function getExecutiveSummary(?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $directorates = Directorate::active()->ordered()->get();
        $summary = [
            'total_revenue' => 0,
            'total_budget' => 0,
            'avg_completion' => 0,
            'total_risks' => 0,
            'high_risks' => 0,
            'avg_uptime' => 0,
            'total_employees' => 0,
            'directorates' => [],
        ];

        $completionSum = 0;
        $uptimeSum = 0;

        foreach ($directorates as $idx => $directorate) {
            mt_srand($this->seed + $directorate->id * 300 + (int) now()->format('Ymd'));

            $revenue = mt_rand(10000000, 500000000);
            $budget = mt_rand(8000000, 450000000);
            $employees = mt_rand(50, 2000);
            $uptime = mt_rand(9500, 10000) / 100;
            $completion = mt_rand(40, 95);
            $riskCount = mt_rand(1, 8);
            $highRisks = mt_rand(0, min(3, $riskCount));

            $summary['total_revenue'] += $revenue;
            $summary['total_budget'] += $budget;
            $summary['total_risks'] += $riskCount;
            $summary['high_risks'] += $highRisks;
            $summary['total_employees'] += $employees;
            $completionSum += $completion;
            $uptimeSum += $uptime;

            $summary['directorates'][] = [
                'id' => $directorate->id,
                'name' => $directorate->name,
                'code' => $directorate->code,
                'slug' => $directorate->slug,
                'color' => $directorate->color,
                'revenue' => $revenue,
                'budget' => $budget,
                'budget_utilization' => round(($revenue / max($budget, 1)) * 100, 1),
                'completion_percentage' => $completion,
                'employees' => $employees,
                'uptime' => $uptime,
                'risk_count' => $riskCount,
                'high_risk_count' => $highRisks,
                'risk_exposure' => round($highRisks / max($riskCount, 1) * 100, 1),
            ];
        }

        $count = max(count($directorates), 1);
        $summary['avg_completion'] = round($completionSum / $count, 1);
        $summary['avg_uptime'] = round($uptimeSum / $count, 2);

        mt_srand();
        return $summary;
    }

    public function getKpiTrend(int $kpiId, int $directorateId, int $periods = 12): array
    {
        $kpi = Kpi::find($kpiId);
        if (!$kpi) {
            return [];
        }

        // Prefer persisted simulation entries; fall back to manual; then synthetic.
        $entries = KpiEntry::where('kpi_id', $kpiId)
            ->where('directorate_id', $directorateId)
            ->where('source', 'simulation')
            ->orderBy('period_date')
            ->limit($periods)
            ->get();

        if ($entries->isEmpty()) {
            $entries = KpiEntry::where('kpi_id', $kpiId)
                ->where('directorate_id', $directorateId)
                ->where('source', 'manual')
                ->orderBy('period_date')
                ->limit($periods)
                ->get();
        }

        if ($entries->isNotEmpty()) {
            return $entries
                ->map(fn (KpiEntry $entry) => [
                    'period' => $entry->period_date->format('Y-m'),
                    'label' => $entry->period_date->format('M Y'),
                    'value' => (float) $entry->value,
                ])
                ->toArray();
        }

        $trend = [];
        $baseValue = $this->getBaseValue($kpi);

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $periodSeed = $this->seed + $kpiId + $directorateId + crc32($date->format('Y-m'));
            mt_srand($periodSeed);

            // Simulate gradual trend with noise
            $trendFactor = 1 + (($periods - $i) * 0.005); // slight upward trend
            $noise = (mt_rand(-500, 500) / 10000); // ±5% noise
            $value = round($baseValue * $trendFactor * (1 + $noise), 2);

            $trend[] = [
                'period' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'value' => $value,
            ];
        }

        mt_srand();
        return $trend;
    }

    public function getDirectorateComparison(array $directorateIds, array $kpiIds): array
    {
        $comparison = [];

        foreach ($directorateIds as $dId) {
            $directorate = Directorate::find($dId);
            if (!$directorate) continue;

            $kpiData = [];
            foreach ($kpiIds as $kpiId) {
                $kpi = Kpi::find($kpiId);
                if (!$kpi) continue;

                mt_srand($this->seed + $dId + $kpiId + (int) now()->format('Ymd'));
                $value = $this->getBaseValue($kpi) * (1 + mt_rand(-1000, 1000) / 10000);

                $kpiData[] = [
                    'kpi_id' => $kpiId,
                    'kpi_name' => $kpi->name,
                    'value' => round($value, 2),
                    'formatted' => $kpi->formatValue($value),
                ];
            }

            $comparison[] = [
                'directorate_id' => $dId,
                'directorate_name' => $directorate->name,
                'kpis' => $kpiData,
            ];
        }

        mt_srand();
        return $comparison;
    }

    // ── Private Helpers ────────────────────────────────────

    private function initializeBaseValues(): void
    {
        $this->baseValues = [
            'financial' => ['min' => 5000000, 'max' => 100000000],
            'operational' => ['min' => 70, 'max' => 100],
            'strategic' => ['min' => 30, 'max' => 100],
            'risk' => ['min' => 1, 'max' => 25],
            'hr' => ['min' => 50, 'max' => 5000],
            'customer' => ['min' => 10000, 'max' => 5000000],
            'project' => ['min' => 0, 'max' => 100],
            'technical' => ['min' => 90, 'max' => 100],
        ];
    }

    private function getBaseValue(Kpi $kpi): float
    {
        $range = $this->baseValues[$kpi->category] ?? ['min' => 0, 'max' => 100];

        return match ($kpi->unit) {
            'percentage' => mt_rand(max(0, (int)$range['min']), min(100, (int)$range['max'])),
            'currency' => mt_rand((int)$range['min'], (int)$range['max']),
            default => mt_rand((int)$range['min'], (int)$range['max']),
        };
    }

    private function generateKpiValue(Kpi $kpi, int $directorateId, int $offset = 0): float
    {
        $dateSeed = (int) Carbon::now()->addMonths($offset)->format('Ymd');
        mt_srand($this->seed + $kpi->id + $directorateId + $dateSeed);

        $value = $this->getBaseValue($kpi);
        mt_srand();

        return round($value, 2);
    }

    private function simulateValue(int $seed, float $min, float $max, string $pattern): float
    {
        mt_srand($seed);
        $base = $min + mt_rand(0, (int)(($max - $min) * 100)) / 100;

        $factor = match ($pattern) {
            'gradual_increase' => 1 + (mt_rand(0, 500) / 10000),
            'slight_increase' => 1 + (mt_rand(0, 200) / 10000),
            'slight_decrease' => 1 - (mt_rand(0, 200) / 10000),
            'variable' => 1 + (mt_rand(-500, 500) / 10000),
            'stable' => 1 + (mt_rand(-100, 100) / 10000),
            default => 1,
        };

        mt_srand();
        return $base * $factor;
    }

    private function getMonthRange(?Carbon $from, ?Carbon $to): array
    {
        $from = $from ?? Carbon::now()->subMonths(11)->startOfMonth();
        $to = $to ?? Carbon::now()->endOfMonth();

        $months = [];
        $current = $from->copy()->startOfMonth();

        while ($current <= $to) {
            $months[] = $current->copy();
            $current->addMonth();
        }

        return $months;
    }
}
