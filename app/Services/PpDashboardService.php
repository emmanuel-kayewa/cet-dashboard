<?php

namespace App\Services;

use App\Models\PpFinancial;
use App\Models\PpGridImpactStudy;
use App\Models\PpProject;
use App\Models\PpProgrammeOutput;
use App\Models\PpRisk;
use App\Models\PpSafeguard;
use Illuminate\Support\Collection;

class PpDashboardService
{
    /**
     * All dimension keys that can be used as filters / breakdowns.
     */
    public const DIMENSIONS = [
        'sector', 'sub_sector', 'status', 'rag_status', 'province',
        'district', 'programme', 'funder', 'contractor',
    ];

    /**
     * Human labels for each dimension.
     */
    public const DIMENSION_LABELS = [
        'sector'     => 'Sector',
        'sub_sector' => 'Project Type',
        'status'     => 'Status',
        'rag_status' => 'RAG',
        'province'   => 'Province',
        'district'   => 'District',
        'programme'  => 'Programme',
        'funder'     => 'Funder',
        'contractor' => 'Contractor',
    ];

    // ─── Level 1: Portfolio Overview ──────────────────────────────

    /**
     * Build the top-level overview payload (all projects, unfiltered).
     */
    public function getOverview(): array
    {
        $allProjects = PpProject::all();
        $safeguards  = PpSafeguard::all();

        return [
            'kpis'              => $this->buildKpis($allProjects, $safeguards),
            'sectorBreakdown'   => $this->groupByDimension($allProjects, 'sector'),
            'subSectorBreakdown' => $this->groupByDimension($allProjects, 'sub_sector'),
            'statusBreakdown'   => $this->groupByDimension($allProjects, 'status'),
            'ragBreakdown'      => $this->groupByDimension($allProjects, 'rag_status'),
            'provinceBreakdown' => $this->groupByDimension($allProjects, 'province'),
            'programmeBreakdown' => $this->groupByDimension($allProjects, 'programme'),
            'sectorInvestment'  => $this->buildSectorInvestment($allProjects),
            'sectorCards'       => $this->buildSectorCards($allProjects),
            'recentIssues'      => $this->buildRecentIssues($allProjects),
            'filterOptions'     => $this->buildFilterOptions($allProjects),
            'gridStudiesSummary' => $this->buildGridStudiesSummary(),
        ];
    }

    // ─── Level 2: Explorer (filtered + adaptive breakdowns) ──────

    /**
     * Build the explorer payload for any combination of active filters.
     */
    public function getExplorerData(array $filters): array
    {
        $query = PpProject::query();

        // Apply active filters
        foreach (self::DIMENSIONS as $dim) {
            if (!empty($filters[$dim])) {
                $query->where($dim, $filters[$dim]);
            }
        }

        $filteredProjects = $query->get();
        $allProjects      = PpProject::all(); // for filter option values
        $safeguards       = PpSafeguard::all();

        // Build breakdowns for each UN-APPLIED dimension
        $breakdowns = [];
        foreach (self::DIMENSIONS as $dim) {
            if (empty($filters[$dim])) {
                $breakdown = $this->groupByDimension($filteredProjects, $dim);
                if (count($breakdown) > 1 || (count($breakdown) === 1 && $breakdown[0]['name'] !== null)) {
                    $breakdowns[$dim] = [
                        'label'  => self::DIMENSION_LABELS[$dim],
                        'field'  => $dim,
                        'data'   => $breakdown,
                    ];
                }
            }
        }

        // Sector investment chart for filtered set
        $sectorInvestment = $this->buildSectorInvestment($filteredProjects);

        // Programme outputs for filtered set (if sector filter is Distribution or not set)
        $programmeOutputs = [];
        if (empty($filters['sector']) || $filters['sector'] === 'Distribution') {
            $programmeOutputs = PpProgrammeOutput::all()->map(fn ($o) => [
                'programme'              => $o->programme,
                'period'                 => $o->period,
                'connections_delivered'   => $o->connections_delivered,
                'transformers_energised'  => $o->transformers_energised,
                'jobs_pending_connection' => $o->jobs_pending_connection,
            ])->values()->toArray();
        }

        // Risks scoped to filtered projects
        $projectIds = $filteredProjects->pluck('id');
        $risks = PpRisk::where(function ($q) use ($projectIds) {
            $q->whereIn('pp_project_id', $projectIds)->orWhereNull('pp_project_id');
        })->get();

        $risksByCategory = $risks->groupBy('risk_category')->map(fn ($g, $cat) => [
            'category' => $cat, 'count' => $g->count(),
        ])->values()->toArray();

        $risksByLevel = $risks->groupBy('risk_level')->map(fn ($g, $lvl) => [
            'level' => $lvl, 'count' => $g->count(),
        ])->values()->toArray();

        return [
            'kpis'              => $this->buildKpis($filteredProjects, $safeguards),
            'breakdowns'        => $breakdowns,
            'sectorInvestment'  => $sectorInvestment,
            'programmeOutputs'  => $programmeOutputs,
            'risksByCategory'   => $risksByCategory,
            'risksByLevel'      => $risksByLevel,
            'projects'          => $this->buildProjectList($filteredProjects),
            'totalCount'        => $filteredProjects->count(),
            'appliedFilters'    => $filters,
            'filterOptions'     => $this->buildFilterOptions($allProjects),
        ];
    }

    // ─── Level 3: Project Detail ─────────────────────────────────

    /**
     * Build the full detail payload for a single project.
     */
    public function getProjectDetail(PpProject $project): array
    {
        $project->load(['milestones', 'financials', 'risks', 'safeguards', 'gridImpactStudies']);

        $milestones = $project->milestones->sortBy('actual_date')->map(fn ($m) => [
            'id'          => $m->id,
            'code'        => $m->milestone_code,
            'milestone'   => $m->milestone,
            'actual_date' => $m->actual_date?->format('Y-m-d'),
            'status'      => $m->status,
            'notes'       => $m->notes,
        ])->values()->toArray();

        $financials = $project->financials->sortByDesc('as_of_date')->map(fn ($f) => [
            'id'               => $f->id,
            'code'             => $f->finance_code,
            'as_of_date'       => $f->as_of_date?->format('Y-m-d'),
            'committed_amount' => $f->committed_amount,
            'paid_to_date'     => $f->paid_to_date,
            'currency'         => $f->currency,
            'notes'            => $f->notes,
        ])->values()->toArray();

        $risks = $project->risks->sortByDesc('severity')->map(fn ($r) => [
            'id'          => $r->id,
            'code'        => $r->risk_code,
            'category'    => $r->risk_category,
            'description' => $r->risk_description,
            'likelihood'  => $r->likelihood,
            'impact'      => $r->impact,
            'severity'    => $r->severity,
            'level'       => $r->risk_level,
            'status'      => $r->status,
            'mitigation'  => $r->mitigation,
            'owner'       => $r->owner,
            'due_date'    => $r->due_date?->format('Y-m-d'),
        ])->values()->toArray();

        $safeguards = $project->safeguards->map(fn ($s) => [
            'id'                 => $s->id,
            'code'               => $s->record_code,
            'scope'              => $s->scope,
            'wayleave_received'  => $s->wayleave_received,
            'wayleave_cleared'   => $s->wayleave_cleared,
            'wayleave_pending'   => $s->wayleave_pending,
            'survey_received'    => $s->survey_received,
            'survey_cleared'     => $s->survey_cleared,
            'survey_pending'     => $s->survey_pending,
            'paps'               => $s->paps,
            'comp_paid_zmw'      => $s->comp_paid_zmw,
        ])->values()->toArray();

        // Financial summary
        $totalCommitted = $project->financials->where('currency', 'USD')->sum('committed_amount');
        $totalPaid      = $project->financials->where('currency', 'USD')->sum('paid_to_date');
        $burnRate       = $totalCommitted > 0 ? round(($totalPaid / $totalCommitted) * 100, 1) : 0;

        // Milestone summary
        $totalMilestones     = $project->milestones->count();
        $completedMilestones = $project->milestones->where('status', 'Completed')->count();
        $milestoneProgress   = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100, 1) : 0;

        return [
            'project' => [
                'id'               => $project->id,
                'code'             => $project->project_code,
                'name'             => $project->project_name,
                'sector'           => $project->sector,
                'sub_sector'       => $project->sub_sector,
                'status'           => $project->status,
                'programme'        => $project->programme,
                'province'         => $project->province,
                'district'         => $project->district,
                'contractor'       => $project->contractor,
                'developer'        => $project->developer,
                'funder'           => $project->funder,
                'funding_type'     => $project->funding_type,
                'cost_usd'         => $project->cost_usd,
                'cost_zmw'         => $project->cost_zmw,
                'capacity_mw'      => $project->capacity_mw,
                'progress_pct'     => $project->progress_pct,
                'cod_planned'      => $project->cod_planned?->format('Y-m-d'),
                'key_issue_summary' => $project->key_issue_summary,
                'last_update_date' => $project->last_update_date?->format('Y-m-d'),
                'rag_status'       => $project->rag_status,
                'notes'            => $project->notes,
            ],
            'milestones'       => $milestones,
            'financials'       => $financials,
            'risks'            => $risks,
            'safeguards'       => $safeguards,
            'gridStudies'      => $this->buildProjectGridStudies($project),
            'summary'          => [
                'totalCommitted'      => $totalCommitted,
                'totalPaid'           => $totalPaid,
                'burnRate'            => $burnRate,
                'totalMilestones'     => $totalMilestones,
                'completedMilestones' => $completedMilestones,
                'milestoneProgress'   => $milestoneProgress,
                'openRisks'           => collect($risks)->where('status', 'Open')->count(),
                'totalRisks'          => count($risks),
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Build top-level KPI array.
     */
    private function buildKpis(Collection $projects, Collection $safeguards): array
    {
        $totalCommitted = round($projects->sum('cost_usd'), 2);
        $totalPaid      = round(
            PpFinancial::whereIn('pp_project_id', $projects->pluck('id'))
                ->where('currency', 'USD')
                ->sum('paid_to_date'),
            2
        );
        $spendPct = $totalCommitted > 0 ? round(($totalPaid / $totalCommitted) * 100, 1) : 0;

        // Sum commissioned MW with smart fallback logic
        $genCommissioned = $projects->where('sector', 'Generation')
            ->filter(fn ($p) => $p->commissioned_mw !== null || 
                               str_contains(strtolower($p->status ?? ''), 'commission') || 
                               ($p->progress_pct ?? 0) >= 100)
            ->sum(fn ($p) => $p->commissioned_mw ?? $p->capacity_mw ?? 0);

        $totalWlReceived = $safeguards->sum('wayleave_received');
        $totalWlCleared  = $safeguards->sum('wayleave_cleared');
        $wlClosurePct    = $totalWlReceived > 0 ? round(($totalWlCleared / $totalWlReceived) * 100, 1) : 0;

        $totalSvReceived = $safeguards->sum('survey_received');
        $totalSvCleared  = $safeguards->sum('survey_cleared');
        $svClosurePct    = $totalSvReceived > 0 ? round(($totalSvCleared / $totalSvReceived) * 100, 1) : 0;

        return [
            'totalProjects'    => $projects->count(),
            'totalCommitted'   => $totalCommitted,
            'totalPaid'        => $totalPaid,
            'spendPct'         => $spendPct,
            'genCommissioned'  => round($genCommissioned, 1),
            'avgProgress'      => $projects->count() > 0 ? round($projects->avg('progress_pct'), 1) : 0,
            'wlClosurePct'     => $wlClosurePct,
            'svClosurePct'     => $svClosurePct,
            'as_of'            => $projects->max('last_update_date'),
        ];
    }

    /**
     * Group projects by a dimension and return [{name, count, totalCost, avgProgress}].
     */
    private function groupByDimension(Collection $projects, string $dimension): array
    {
        $colors = ['#6889c4', '#5ba5b5', '#7cae9a', '#d4a24e', '#c47878', '#9b8ec4', '#e09874', '#6aaeae', '#b5a276', '#8fafd0'];

        // RAG gets special colors
        $ragColors = [
            'Green' => '#4ead7a',
            'Amber' => '#d4a24e',
            'Red'   => '#cf6060',
        ];

        return $projects->groupBy($dimension)->map(function ($group, $key) use ($dimension, $colors, $ragColors) {
            static $idx = 0;
            $name = $key ?: 'Unknown';
            $color = match ($dimension) {
                'rag_status' => $ragColors[$name] ?? '#94a3b8',
                default      => $colors[$idx++ % count($colors)],
            };

            return [
                'name'        => $name,
                'value'       => $group->count(),
                'totalCost'   => round($group->sum('cost_usd'), 2),
                'avgProgress' => round($group->avg('progress_pct'), 1),
                'color'       => $color,
            ];
        })->values()->toArray();
    }

    /**
     * Build sector investment bar-chart data.
     */
    private function buildSectorInvestment(Collection $projects): array
    {
        return $projects->groupBy('sector')->map(function ($group, $sector) {
            return [
                'sector'        => $sector,
                'committed'     => round($group->sum('cost_usd'), 2),
                'paid'          => round(
                    PpFinancial::whereIn('pp_project_id', $group->pluck('id'))
                        ->where('currency', 'USD')
                        ->sum('paid_to_date'),
                    2
                ),
                'project_count' => $group->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Build sector quick-access cards.
     */
    private function buildSectorCards(Collection $projects): array
    {
        $sectorColors = [
            'Generation'   => '#4ead7a',
            'Transmission' => '#6889c4',
            'Distribution' => '#d4a24e',
            'IPP'          => '#9b8ec4',
        ];

        return $projects->groupBy('sector')->map(function ($group, $sector) use ($sectorColors) {
            $totalMw = round($group->sum('capacity_mw'), 1);
            return [
                'sector'       => $sector,
                'color'        => $sectorColors[$sector] ?? '#64748b',
                'projectCount' => $group->count(),
                'totalCost'    => round($group->sum('cost_usd'), 2),
                'avgProgress'  => round($group->avg('progress_pct'), 1),
                'totalMw'      => $totalMw,
                'ragCounts'    => [
                    'Green' => $group->where('rag_status', 'Green')->count(),
                    'Amber' => $group->where('rag_status', 'Amber')->count(),
                    'Red'   => $group->where('rag_status', 'Red')->count(),
                ],
            ];
        })->values()->toArray();
    }

    /**
     * Top 5 urgent projects (Red RAG or with key issues).
     */
    private function buildRecentIssues(Collection $projects): array
    {
        return $projects
            ->filter(fn ($p) => $p->rag_status === 'Red' || !empty($p->key_issue_summary))
            ->sortByDesc(fn ($p) => $p->rag_status === 'Red' ? 1 : 0)
            ->take(5)
            ->map(fn ($p) => [
                'id'        => $p->id,
                'code'      => $p->project_code,
                'name'      => $p->project_name,
                'sector'    => $p->sector,
                'rag'       => $p->rag_status,
                'key_issue' => $p->key_issue_summary,
                'progress'  => $p->progress_pct,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Build filter option dropdowns from ALL projects (unfiltered).
     */
    private function buildFilterOptions(Collection $projects): array
    {
        $options = [];
        foreach (self::DIMENSIONS as $dim) {
            $options[$dim] = $projects->pluck($dim)
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }
        return $options;
    }

    /**
     * Build paginated project list for tables.
     */
    private function buildProjectList(Collection $projects): array
    {
        return $projects->sortByDesc('cost_usd')->map(fn ($p) => [
            'id'           => $p->id,
            'code'         => $p->project_code,
            'name'         => $p->project_name,
            'sector'       => $p->sector,
            'sub_sector'   => $p->sub_sector,
            'status'       => $p->status,
            'programme'    => $p->programme,
            'province'     => $p->province,
            'district'     => $p->district,
            'contractor'   => $p->contractor,
            'funder'       => $p->funder,
            'capacity_mw'  => $p->capacity_mw,
            'cost_usd'     => $p->cost_usd,
            'progress_pct' => $p->progress_pct,
            'rag'          => $p->rag_status,
            'key_issue'    => $p->key_issue_summary,
            'cod_planned'  => $p->cod_planned?->format('Y-m-d'),
        ])->values()->toArray();
    }

    // ─── Grid Impact Studies helpers ─────────────────────────────

    /**
     * Build the compact grid studies summary for the overview page.
     */
    private function buildGridStudiesSummary(): array
    {
        $allStudies = PpGridImpactStudy::all();
        $reports    = $allStudies->where('study_type', 'report');
        $inception  = $allStudies->where('study_type', 'inception');

        $stageFields = ['stage_received', 'stage_not_started', 'stage_under_review', 'stage_pending_client', 'stage_revisions', 'stage_approved'];
        $stageLabels = ['Received', 'Not Started', 'Under Review', 'Pending Client', 'Revisions', 'Approved'];

        $buildFunnel = function (Collection $studies) use ($stageLabels) {
            $funnel = [];
            foreach ($stageLabels as $label) {
                $funnel[] = [
                    'stage' => $label,
                    'count' => $studies->filter(fn($s) => $s->current_stage === $label)->count(),
                ];
            }
            return $funnel;
        };

        // Technology breakdown
        $techBreakdown = $allStudies->groupBy('technology')->map(fn ($g, $tech) => [
            'name'       => $tech ?: 'Unknown',
            'value'      => $g->count(),
            'totalMw'    => round($g->sum('capacity_mw'), 1),
        ])->values()->toArray();

        return [
            'totalStudies'     => $allStudies->count(),
            'totalReports'     => $reports->count(),
            'totalInception'   => $inception->count(),
            'approvedCount'    => $allStudies->filter(fn($s) => $s->current_stage === 'Approved')->count(),
            'avgProgress'      => $allStudies->count() > 0 ? round($allStudies->avg('progress_pct'), 1) : 0,
            'totalCapacityMw'  => round($allStudies->sum('capacity_mw'), 1),
            'reportFunnel'     => $buildFunnel($reports),
            'inceptionFunnel'  => $buildFunnel($inception),
            'techBreakdown'    => $techBreakdown,
        ];
    }

    /**
     * Build the full grid impact studies page data (with optional filters).
     */
    public function getGridStudiesData(array $filters = []): array
    {
        $query = PpGridImpactStudy::with('project');

        if (!empty($filters['study_type'])) {
            $query->where('study_type', $filters['study_type']);
        }
        if (!empty($filters['technology'])) {
            $query->where('technology', $filters['technology']);
        }
        if (!empty($filters['project_area'])) {
            $query->where('project_area', $filters['project_area']);
        }

        $studies = $query->get();

        // Filter by current stage (not just any reached stage)
        if (!empty($filters['stage'])) {
            $stageLabelMap = [
                'received'       => 'Received',
                'not_started'    => 'Not Started',
                'under_review'   => 'Under Review',
                'pending_client' => 'Pending Client',
                'revisions'      => 'Revisions',
                'approved'       => 'Approved',
            ];
            if (isset($stageLabelMap[$filters['stage']])) {
                $targetStage = $stageLabelMap[$filters['stage']];
                $studies = $studies->filter(fn($s) => $s->current_stage === $targetStage);
            }
        }

        $stageFields = ['stage_received', 'stage_not_started', 'stage_under_review', 'stage_pending_client', 'stage_revisions', 'stage_approved'];
        $stageLabels = ['Received', 'Not Started', 'Under Review', 'Pending Client', 'Revisions', 'Approved'];
        $stageColors = ['#6889c4', '#94a3b8', '#d4a24e', '#e09874', '#c47878', '#4ead7a'];

        // Stage funnel (counts only current stage for each study)
        $stageFunnel = [];
        foreach ($stageLabels as $i => $label) {
            $stageFunnel[] = [
                'stage' => $label,
                'count' => $studies->filter(fn($s) => $s->current_stage === $label)->count(),
                'color' => $stageColors[$i],
            ];
        }

        // Technology pie
        $techColors = ['#6889c4', '#5ba5b5', '#7cae9a', '#d4a24e', '#c47878', '#9b8ec4', '#e09874'];
        $techPie = $studies->groupBy('technology')->map(function ($g, $tech) use (&$techColors) {
            static $idx = 0;
            return [
                'name'    => $tech ?: 'Unknown',
                'value'   => $g->count(),
                'totalMw' => round($g->sum('capacity_mw'), 1),
                'color'   => $techColors[$idx++ % count($techColors)],
            ];
        })->values()->toArray();

        // Project area breakdown
        $areaBreakdown = $studies->groupBy('project_area')->map(fn ($g, $area) => [
            'name'  => $area ?: 'Unknown',
            'value' => $g->count(),
            'totalMw' => round($g->sum('capacity_mw'), 1),
        ])->values()->toArray();

        // Study type breakdown
        $typeBreakdown = $studies->groupBy('study_type')->map(fn ($g, $type) => [
            'name'  => ucfirst($type),
            'value' => $g->count(),
            'color' => $type === 'report' ? '#6889c4' : '#9b8ec4',
        ])->values()->toArray();

        // Progress histogram (buckets: 0-20, 20-40, 40-60, 60-80, 80-100)
        $buckets = ['0-20%' => 0, '20-40%' => 0, '40-60%' => 0, '60-80%' => 0, '80-100%' => 0];
        foreach ($studies as $s) {
            $pct = $s->progress_pct ?? 0;
            if ($pct < 20) $buckets['0-20%']++;
            elseif ($pct < 40) $buckets['20-40%']++;
            elseif ($pct < 60) $buckets['40-60%']++;
            elseif ($pct < 80) $buckets['60-80%']++;
            else $buckets['80-100%']++;
        }
        $progressHistogram = collect($buckets)->map(fn ($count, $label) => ['name' => $label, 'value' => $count])->values()->toArray();

        // All studies for table
        $studyList = $studies->sortByDesc('capacity_mw')->map(fn ($s) => [
            'id'                  => $s->id,
            'study_code'          => $s->study_code,
            'study_type'          => $s->study_type,
            'name'                => $s->name,
            'capacity_mw'         => $s->capacity_mw,
            'developer'           => $s->developer,
            'technology'          => $s->technology,
            'project_area'        => $s->project_area,
            'point_of_connection' => $s->point_of_connection,
            'progress_pct'        => $s->progress_pct,
            'current_stage'       => $s->current_stage,
            'stage_received'      => $s->stage_received,
            'stage_not_started'   => $s->stage_not_started,
            'stage_under_review'  => $s->stage_under_review,
            'stage_pending_client' => $s->stage_pending_client,
            'stage_revisions'     => $s->stage_revisions,
            'stage_approved'      => $s->stage_approved,
            'project_id'          => $s->project?->id,
            'project_code'        => $s->project?->project_code,
            'project_name'        => $s->project?->project_name,
        ])->values()->toArray();

        // Filter options
        $allStudies = PpGridImpactStudy::all();
        $filterOptions = [
            'study_type'   => ['report', 'inception'],
            'technology'   => $allStudies->pluck('technology')->filter()->unique()->sort()->values()->toArray(),
            'project_area' => $allStudies->pluck('project_area')->filter()->unique()->sort()->values()->toArray(),
            'stage'        => ['received', 'not_started', 'under_review', 'pending_client', 'revisions', 'approved'],
        ];

        return [
            'kpis' => [
                'totalStudies'    => $studies->count(),
                'approvedCount'   => $studies->filter(fn($s) => $s->current_stage === 'Approved')->count(),
                'avgProgress'     => $studies->count() > 0 ? round($studies->avg('progress_pct'), 1) : 0,
                'totalCapacityMw' => round($studies->sum('capacity_mw'), 1),
                'underReview'     => $studies->filter(fn($s) => $s->current_stage === 'Under Review')->count(),
                'pendingClient'   => $studies->filter(fn($s) => $s->current_stage === 'Pending Client')->count(),
            ],
            'stageFunnel'        => $stageFunnel,
            'techPie'            => $techPie,
            'areaBreakdown'      => $areaBreakdown,
            'typeBreakdown'      => $typeBreakdown,
            'progressHistogram'  => $progressHistogram,
            'studies'            => $studyList,
            'totalCount'         => $studies->count(),
            'appliedFilters'     => $filters,
            'filterOptions'      => $filterOptions,
        ];
    }

    /**
     * Build grid impact studies array for a specific project's detail page.
     */
    private function buildProjectGridStudies(PpProject $project): array
    {
        return $project->gridImpactStudies->map(fn ($s) => [
            'id'                   => $s->id,
            'study_code'           => $s->study_code,
            'study_type'           => $s->study_type,
            'name'                 => $s->name,
            'capacity_mw'          => $s->capacity_mw,
            'developer'            => $s->developer,
            'technology'           => $s->technology,
            'project_area'         => $s->project_area,
            'point_of_connection'  => $s->point_of_connection,
            'progress_pct'         => $s->progress_pct,
            'current_stage'        => $s->current_stage,
            'stage_received'       => $s->stage_received,
            'stage_not_started'    => $s->stage_not_started,
            'stage_under_review'   => $s->stage_under_review,
            'stage_pending_client' => $s->stage_pending_client,
            'stage_revisions'      => $s->stage_revisions,
            'stage_approved'       => $s->stage_approved,
        ])->values()->toArray();
    }
}
