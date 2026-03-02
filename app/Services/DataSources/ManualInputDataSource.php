<?php

namespace App\Services\DataSources;

use App\Models\Directorate;
use App\Models\FinancialEntry;
use App\Models\Kpi;
use App\Models\KpiEntry;
use App\Models\Project;
use App\Models\Risk;
use Carbon\Carbon;

class ManualInputDataSource implements DataSourceInterface
{
    public function getIdentifier(): string
    {
        return 'manual';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getKpiData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $query = KpiEntry::with('kpi')
            ->where('directorate_id', $directorateId)
            ->where('source', 'manual');

        if ($fromDate) $query->where('period_date', '>=', $fromDate);
        if ($toDate) $query->where('period_date', '<=', $toDate);

        $entries = $query->orderByDesc('period_date')
            ->get()
            ->groupBy('kpi_id');

        $data = [];
        foreach ($entries as $kpiId => $kpiEntries) {
            $latest = $kpiEntries->first();
            $previous = $kpiEntries->skip(1)->first();
            $kpi = $latest->kpi;

            $data[] = [
                'kpi_id' => $kpi->id,
                'kpi_name' => $kpi->name,
                'kpi_slug' => $kpi->slug,
                'category' => $kpi->category,
                'value' => (float) $latest->value,
                'previous_value' => $previous ? (float) $previous->value : null,
                'target' => (float) ($kpi->target_value ?? 0),
                'unit' => $kpi->unit,
                'trend_direction' => $kpi->trend_direction,
                'change_percentage' => $latest->getChangePercentage(),
                'status' => $kpi->getStatusForValue($latest->value),
                'formatted_value' => $kpi->formatValue($latest->value),
            ];
        }

        return $data;
    }

    public function getFinancialData(int $directorateId, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $query = FinancialEntry::where('directorate_id', $directorateId)
            ->where('source', 'manual');

        if ($fromDate) $query->where('period_date', '>=', $fromDate);
        if ($toDate) $query->where('period_date', '<=', $toDate);

        $entries = $query->orderBy('period_date')->get();

        $grouped = $entries->groupBy(fn ($e) => $e->period_date->format('Y-m'));
        $data = [];

        foreach ($grouped as $period => $periodEntries) {
            $revenue = $periodEntries->where('category', 'revenue')->sum('amount');
            $budget = $periodEntries->where('category', 'budget')->sum('amount');
            $opex = $periodEntries->where('category', 'opex')->sum('amount');
            $capex = $periodEntries->where('category', 'capex')->sum('amount');

            $data[] = [
                'period' => $period,
                'period_label' => Carbon::parse($period . '-01')->format('M Y'),
                'revenue' => round($revenue, 2),
                'budget' => round($budget, 2),
                'opex' => round($opex, 2),
                'capex' => round($capex, 2),
                'budget_utilization' => $budget > 0 ? round(($opex + $capex) / $budget * 100, 1) : 0,
                'net_position' => round($revenue - $opex - $capex, 2),
            ];
        }

        return $data;
    }

    public function getProjectData(int $directorateId): array
    {
        return Project::where('directorate_id', $directorateId)
            ->where('source', 'manual')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'priority' => $p->priority,
                'budget' => (float) $p->budget,
                'spent' => (float) $p->spent,
                'completion_percentage' => $p->completion_percentage,
                'start_date' => $p->start_date?->format('Y-m-d'),
                'end_date' => $p->end_date?->format('Y-m-d'),
                'is_overdue' => $p->isOverdue(),
                'days_remaining' => $p->getDaysRemaining(),
            ])
            ->toArray();
    }

    public function getRiskData(int $directorateId): array
    {
        return Risk::where('directorate_id', $directorateId)
            ->where('source', 'manual')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Risk $r) => [
                'id' => $r->id,
                'title' => $r->title,
                'category' => $r->category,
                'likelihood' => $r->likelihood,
                'impact' => $r->impact,
                'risk_score' => $r->likelihood * $r->impact,
                'level' => $r->getRiskLevel(),
                'status' => $r->status,
            ])
            ->toArray();
    }

    public function getExecutiveSummary(?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $directorates = Directorate::active()->ordered()->get();
        $directorateIds = $directorates->pluck('id')->toArray();

        // Pre-load all financial data in one query set
        $financialQuery = FinancialEntry::whereIn('directorate_id', $directorateIds)
            ->where('source', 'manual');
        if ($fromDate) $financialQuery->where('period_date', '>=', $fromDate);
        if ($toDate) $financialQuery->where('period_date', '<=', $toDate);
        $financialEntries = $financialQuery->get()->groupBy('directorate_id');

        // Pre-load all projects and risks in bulk
        $allProjects = Project::whereIn('directorate_id', $directorateIds)
            ->where('source', 'manual')
            ->get()
            ->groupBy('directorate_id');

        $allRisks = Risk::whereIn('directorate_id', $directorateIds)
            ->where('source', 'manual')
            ->get()
            ->groupBy('directorate_id');

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

        foreach ($directorates as $directorate) {
            $dFinancials = $financialEntries->get($directorate->id, collect());
            $revenue = $dFinancials->where('category', 'revenue')->sum('amount');
            $budget = $dFinancials->where('category', 'budget')->sum('amount');

            $dProjects = $allProjects->get($directorate->id, collect());
            $avgCompletion = $dProjects->avg('completion_percentage') ?? 0;

            $dRisks = $allRisks->get($directorate->id, collect());
            $riskCount = $dRisks->count();
            $highRisks = $dRisks->filter(fn($r) => $r->getRiskLevel() === 'critical' || $r->getRiskLevel() === 'high')->count();

            $summary['total_revenue'] += $revenue;
            $summary['total_budget'] += $budget;
            $summary['total_risks'] += $riskCount;
            $summary['high_risks'] += $highRisks;

            $summary['directorates'][] = [
                'id' => $directorate->id,
                'name' => $directorate->name,
                'code' => $directorate->code,
                'slug' => $directorate->slug,
                'color' => $directorate->color,
                'revenue' => $revenue,
                'budget' => $budget,
                'budget_utilization' => $budget > 0 ? round($revenue / $budget * 100, 1) : 0,
                'completion_percentage' => round($avgCompletion, 1),
                'risk_count' => $riskCount,
                'high_risk_count' => $highRisks,
            ];
        }

        $count = max(count($directorates), 1);
        $summary['avg_completion'] = round(
            collect($summary['directorates'])->avg('completion_percentage') ?? 0, 1
        );

        return $summary;
    }

    public function getKpiTrend(int $kpiId, int $directorateId, int $periods = 12): array
    {
        return KpiEntry::where('kpi_id', $kpiId)
            ->where('directorate_id', $directorateId)
            ->where('source', 'manual')
            ->orderBy('period_date')
            ->limit($periods)
            ->get()
            ->map(fn ($entry) => [
                'period' => $entry->period_date->format('Y-m'),
                'label' => $entry->period_date->format('M Y'),
                'value' => (float) $entry->value,
            ])
            ->toArray();
    }

    public function getDirectorateComparison(array $directorateIds, array $kpiIds): array
    {
        // Pre-load all directorates and KPIs in bulk
        $directorates = Directorate::whereIn('id', $directorateIds)->get()->keyBy('id');
        $kpis = Kpi::whereIn('id', $kpiIds)->get()->keyBy('id');

        // Pre-load all latest entries in one query using a subquery for max period_date
        $latestEntries = KpiEntry::whereIn('kpi_id', $kpiIds)
            ->whereIn('directorate_id', $directorateIds)
            ->where('source', 'manual')
            ->orderByDesc('period_date')
            ->get()
            ->groupBy(fn($e) => $e->directorate_id . '-' . $e->kpi_id)
            ->map(fn($entries) => $entries->first());

        $comparison = [];

        foreach ($directorateIds as $dId) {
            $directorate = $directorates->get($dId);
            if (!$directorate) continue;

            $kpiData = [];
            foreach ($kpiIds as $kpiId) {
                $kpi = $kpis->get($kpiId);
                if (!$kpi) continue;

                $latest = $latestEntries->get($dId . '-' . $kpiId);

                $kpiData[] = [
                    'kpi_id' => $kpiId,
                    'kpi_name' => $kpi->name,
                    'value' => $latest ? (float) $latest->value : 0,
                    'formatted' => $kpi->formatValue($latest?->value ?? 0),
                ];
            }

            $comparison[] = [
                'directorate_id' => $dId,
                'directorate_name' => $directorate->name,
                'kpis' => $kpiData,
            ];
        }

        return $comparison;
    }
}
