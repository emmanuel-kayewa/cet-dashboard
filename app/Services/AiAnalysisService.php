<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\KpiEntry;
use App\Models\Incident;
use App\Models\Risk;
use App\Services\AI\AiProviderManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private AiProviderManager $ai;
    private DashboardService $dashboard;

    public function __construct(AiProviderManager $ai, DashboardService $dashboard)
    {
        $this->ai = $ai;
        $this->dashboard = $dashboard;
    }

    /**
     * Check if AI analysis is available.
     */
    public function isAvailable(): bool
    {
        return config('dashboard.ai.enabled', true) && $this->ai->isAvailable();
    }

    // ═══════════════════════════════════════════════════════════
    //  A — Executive Insights
    // ═══════════════════════════════════════════════════════════

    /**
     * Generate an AI-written executive summary with key insights and concerns.
     */
    public function generateExecutiveInsights(bool $fresh = false): array
    {
        $cacheKey = 'ai_executive_insights';
        $ttl = config('dashboard.ai.cache.executive_insights', 1440);

        if (!$fresh && $cached = $this->getCache($cacheKey)) {
            return $cached;
        }

        $contextData = $this->gatherExecutiveContext();

        $systemPrompt = <<<'PROMPT'
You are a senior business intelligence analyst at ZESCO Limited, Zambia's primary electricity utility company.
Analyze the provided KPI data and generate an executive briefing.

Your response must be valid JSON with this structure:
{
  "summary": "A 2-3 paragraph executive narrative highlighting the overall state of the organisation",
  "key_concerns": ["concern 1", "concern 2", ...],
  "positive_highlights": ["highlight 1", "highlight 2", ...],
  "recommendations": ["recommendation 1", "recommendation 2", ...],
  "risk_assessment": "brief overall risk assessment",
  "outlook": "forward-looking statement about trends"
}

Be specific with numbers. Reference actual directorate names and KPI values. Be concise but insightful.
PROMPT;

        $result = $this->ai->provider()->chatWithJson($systemPrompt, json_encode($contextData));

        if (!empty($result)) {
            $result['generated_at'] = now()->toISOString();
            $result['provider'] = $this->ai->getIdentifier();
            $this->setCache($cacheKey, $result, $ttl);
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    //  B — Anomaly Explanation
    // ═══════════════════════════════════════════════════════════

    /**
     * Explain why a KPI anomaly occurred by cross-referencing related data.
     */
    public function explainAnomaly(int $kpiId, int $directorateId): array
    {
        $cacheKey = "ai_anomaly_{$kpiId}_{$directorateId}";
        $ttl = config('dashboard.ai.cache.anomaly_explanation', 720);

        if ($cached = $this->getCache($cacheKey)) {
            return $cached;
        }

        $context = $this->gatherAnomalyContext($kpiId, $directorateId);

        $systemPrompt = <<<'PROMPT'
You are a data analyst at ZESCO Limited (Zambia electricity utility).
A KPI anomaly has been detected. Analyze all the surrounding data to explain WHY this anomaly likely occurred.

Cross-reference with:
- Other KPIs in the same directorate
- Recent incidents or risks
- Historical trends

Return valid JSON:
{
  "likely_causes": ["cause 1 with explanation", "cause 2 with explanation"],
  "correlated_factors": ["factor 1", "factor 2"],
  "severity_assessment": "low|medium|high|critical",
  "recommended_actions": ["action 1", "action 2"],
  "confidence": "low|medium|high"
}
PROMPT;

        $result = $this->ai->provider()->chatWithJson($systemPrompt, json_encode($context));

        if (!empty($result)) {
            $result['kpi_id'] = $kpiId;
            $result['directorate_id'] = $directorateId;
            $result['generated_at'] = now()->toISOString();
            $this->setCache($cacheKey, $result, $ttl);
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    //  C — Directorate Recommendations
    // ═══════════════════════════════════════════════════════════

    /**
     * Generate actionable suggestions for a directorate.
     */
    public function suggestActions(int $directorateId): array
    {
        $cacheKey = "ai_recommendations_{$directorateId}";
        $ttl = config('dashboard.ai.cache.recommendations', 720);

        if ($cached = $this->getCache($cacheKey)) {
            return $cached;
        }

        $context = $this->gatherDirectorateContext($directorateId);

        $systemPrompt = <<<'PROMPT'
You are a strategic advisor to ZESCO Limited's management.
Analyze the directorate's performance data and provide prioritized, actionable recommendations.

Return valid JSON:
{
  "performance_rating": "excellent|good|needs_attention|critical",
  "summary": "2-3 sentence performance summary",
  "priority_actions": [
    {
      "action": "specific action to take",
      "impact": "expected impact",
      "urgency": "immediate|this_week|this_month|this_quarter",
      "related_kpi": "KPI name this affects"
    }
  ],
  "strengths": ["strength 1", "strength 2"],
  "risks_ahead": ["risk 1", "risk 2"]
}
PROMPT;

        $result = $this->ai->provider()->chatWithJson($systemPrompt, json_encode($context));

        if (!empty($result)) {
            $result['directorate_id'] = $directorateId;
            $result['generated_at'] = now()->toISOString();
            $this->setCache($cacheKey, $result, $ttl);
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    //  D — Natural Language Query
    // ═══════════════════════════════════════════════════════════

    /**
     * Answer a natural language question about the dashboard data.
     */
    public function answerQuery(string $question): array
    {
        $context = $this->gatherQueryContext();

        $systemPrompt = <<<'PROMPT'
You are a helpful data analyst assistant for ZESCO Limited's executive dashboard.
Answer the user's question using the provided dashboard data. Be specific with numbers, directorate names, and KPI values.

If the question cannot be answered with the available data, say so clearly.

Return valid JSON:
{
  "answer": "your detailed answer to the question",
  "data_points": ["key data point 1", "key data point 2"],
  "confidence": "high|medium|low",
  "follow_up_suggestions": ["related question the user might want to ask"]
}
PROMPT;

        $userPrompt = "QUESTION: {$question}\n\nAVAILABLE DATA:\n" . json_encode($context);

        return $this->ai->provider()->chatWithJson($systemPrompt, $userPrompt);
    }

    // ═══════════════════════════════════════════════════════════
    //  E — Predictive Deadline Breach
    // ═══════════════════════════════════════════════════════════

    /**
     * Assess whether a KPI is likely to miss its deadline.
     */
    public function predictDeadlineBreach(int $kpiId, int $directorateId): array
    {
        $kpi = Kpi::find($kpiId);
        if (!$kpi || !$kpi->hasDeadline()) {
            return [];
        }

        $entries = KpiEntry::where('kpi_id', $kpiId)
            ->where('directorate_id', $directorateId)
            ->orderBy('period_date')
            ->get(['value', 'period_date', 'period_type'])
            ->toArray();

        // Also get the linear forecast
        $forecast = $this->dashboard->forecastKpi($kpiId, $directorateId);

        $context = [
            'kpi_name' => $kpi->name,
            'target_value' => $kpi->target_value,
            'target_deadline' => $kpi->target_deadline?->format('Y-m-d'),
            'days_remaining' => $kpi->daysUntilDeadline(),
            'current_milestone' => $kpi->getCurrentMilestone(),
            'trend_direction' => $kpi->trend_direction,
            'unit' => $kpi->unit,
            'historical_entries' => $entries,
            'linear_forecast' => $forecast,
        ];

        $systemPrompt = <<<'PROMPT'
You are a predictive analytics specialist at ZESCO Limited.
Assess whether the given KPI is likely to meet its target by the deadline.

Consider: the trend in historical data, the linear forecast, remaining time, and the gap between current value and target.

Return valid JSON:
{
  "will_meet_target": true/false,
  "probability_of_meeting": 0-100,
  "projected_value_at_deadline": number,
  "gap_analysis": "explanation of the gap between projected and target",
  "acceleration_needed": "what rate of improvement is needed to meet the target",
  "recommendation": "specific action recommendation"
}
PROMPT;

        return $this->ai->provider()->chatWithJson($systemPrompt, json_encode($context));
    }

    // ═══════════════════════════════════════════════════════════
    //  F — KPI Auto-Categorization (for import)
    // ═══════════════════════════════════════════════════════════

    /**
     * Auto-categorize and enrich imported KPI data using AI.
     *
     * @param  array  $rawKpis  Array of raw KPI data (name, description, etc.)
     * @return array  Enriched KPI data with suggested categories, thresholds, etc.
     */
    public function categorizeKpis(array $rawKpis): array
    {
        $categories = implode(', ', array_keys(config('dashboard.kpi_categories', [])));

        $systemPrompt = <<<PROMPT
You are a KPI classification expert for ZESCO Limited, a Zambian electricity utility company.
Given a list of KPIs, classify each one and suggest appropriate settings.

Available categories: {$categories}
Available units: number, percentage, currency, ratio
Available trend directions: up_is_good, down_is_good, neutral

For each KPI, return:
{
  "kpis": [
    {
      "original_name": "the name as provided",
      "suggested_name": "cleaned/standardized name",
      "category": "one of the categories above",
      "unit": "one of the units above",
      "trend_direction": "up_is_good or down_is_good or neutral",
      "suggested_target": number or null,
      "suggested_warning_threshold": number or null,
      "suggested_critical_threshold": number or null,
      "currency_code": "ZMW or USD or null",
      "description": "brief description of what this KPI measures"
    }
  ]
}
PROMPT;

        $result = $this->ai->provider()->chatWithJson($systemPrompt, json_encode(['kpis' => $rawKpis]));

        return $result['kpis'] ?? [];
    }

    // ═══════════════════════════════════════════════════════════
    //  G — Weekly Digest
    // ═══════════════════════════════════════════════════════════

    /**
     * Generate a weekly performance digest for email.
     */
    public function generateWeeklyDigest(): array
    {
        $context = $this->gatherWeeklyContext();

        $systemPrompt = <<<'PROMPT'
You are ZESCO Limited's AI performance analyst.
Generate a concise weekly performance digest for senior leadership.

Return valid JSON:
{
  "headline": "one-line headline for the week",
  "executive_summary": "2-3 paragraph summary of the week's performance",
  "top_performers": [{"directorate": "name", "highlight": "what they did well"}],
  "areas_of_concern": [{"directorate": "name", "concern": "what needs attention"}],
  "kpi_movements": [{"kpi": "name", "movement": "description of change"}],
  "week_ahead_outlook": "what to watch for next week"
}
PROMPT;

        return $this->ai->provider()->chatWithJson($systemPrompt, json_encode($context));
    }

    // ═══════════════════════════════════════════════════════════
    //  Context Gathering (Private)
    // ═══════════════════════════════════════════════════════════

    private function gatherExecutiveContext(): array
    {
        $summary = $this->dashboard->getExecutiveSummary();
        $directorates = Directorate::active()->with(['kpis' => fn($q) => $q->active()])->get();

        $alerts = Alert::unread()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['type', 'severity', 'title', 'message', 'created_at'])
            ->toArray();

        $kpiSummaries = [];
        foreach ($directorates as $dir) {
            $dirKpis = [];
            foreach ($dir->kpis as $kpi) {
                $latest = KpiEntry::where('kpi_id', $kpi->id)
                    ->where('directorate_id', $dir->id)
                    ->orderByDesc('period_date')
                    ->first();

                if ($latest) {
                    $dirKpis[] = [
                        'name' => $kpi->name,
                        'category' => $kpi->category,
                        'value' => $latest->value,
                        'target' => $kpi->target_value,
                        'status' => $kpi->getStatusForValue($latest->value),
                        'change_pct' => $latest->getChangePercentage(),
                        'unit' => $kpi->unit,
                        'deadline' => $kpi->target_deadline?->format('Y-m-d'),
                        'days_to_deadline' => $kpi->daysUntilDeadline(),
                    ];
                }
            }

            $kpiSummaries[] = [
                'directorate' => $dir->name,
                'kpis' => $dirKpis,
            ];
        }

        return [
            'organization_summary' => $summary,
            'directorates' => $kpiSummaries,
            'recent_alerts' => $alerts,
            'analysis_date' => now()->format('Y-m-d'),
        ];
    }

    private function gatherAnomalyContext(int $kpiId, int $directorateId): array
    {
        $kpi = Kpi::find($kpiId);
        $directorate = Directorate::find($directorateId);

        // Get recent entries for this KPI
        $entries = KpiEntry::where('kpi_id', $kpiId)
            ->where('directorate_id', $directorateId)
            ->orderByDesc('period_date')
            ->limit(12)
            ->get(['value', 'previous_value', 'period_date', 'notes'])
            ->toArray();

        // Get other KPIs for the same directorate (for correlation)
        $otherKpis = [];
        $dirKpis = Kpi::whereHas('directorates', fn($q) => $q->where('directorates.id', $directorateId))
            ->where('id', '!=', $kpiId)
            ->active()
            ->get();

        foreach ($dirKpis as $otherKpi) {
            $latest = KpiEntry::where('kpi_id', $otherKpi->id)
                ->where('directorate_id', $directorateId)
                ->orderByDesc('period_date')
                ->first();

            if ($latest) {
                $otherKpis[] = [
                    'name' => $otherKpi->name,
                    'value' => $latest->value,
                    'change_pct' => $latest->getChangePercentage(),
                    'status' => $otherKpi->getStatusForValue($latest->value),
                ];
            }
        }

        // Get recent incidents/risks for this directorate
        $incidents = [];
        if (class_exists(\App\Models\Incident::class)) {
            $incidents = Incident::where('directorate_id', $directorateId)
                ->where('created_at', '>=', now()->subMonths(3))
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['title', 'severity', 'status', 'created_at'])
                ->toArray();
        }

        $risks = Risk::where('directorate_id', $directorateId)
            ->orderByDesc('score')
            ->limit(10)
            ->get(['title', 'category', 'score', 'status'])
            ->toArray();

        return [
            'anomalous_kpi' => [
                'name' => $kpi?->name,
                'category' => $kpi?->category,
                'unit' => $kpi?->unit,
                'target' => $kpi?->target_value,
                'trend_direction' => $kpi?->trend_direction,
            ],
            'directorate' => $directorate?->name,
            'recent_entries' => $entries,
            'other_directorate_kpis' => $otherKpis,
            'recent_incidents' => $incidents,
            'active_risks' => $risks,
        ];
    }

    private function gatherDirectorateContext(int $directorateId): array
    {
        $detail = $this->dashboard->getDirectorateDetail($directorateId);
        $directorate = Directorate::find($directorateId);

        // Get deadline info for KPIs
        $kpisWithDeadlines = Kpi::whereHas('directorates', fn($q) => $q->where('directorates.id', $directorateId))
            ->active()
            ->whereNotNull('target_deadline')
            ->get()
            ->map(fn($kpi) => [
                'name' => $kpi->name,
                'target' => $kpi->target_value,
                'deadline' => $kpi->target_deadline->format('Y-m-d'),
                'days_remaining' => $kpi->daysUntilDeadline(),
                'is_overdue' => $kpi->isOverdue(),
            ])
            ->toArray();

        return [
            'directorate_name' => $directorate?->name,
            'performance_data' => $detail,
            'kpis_with_deadlines' => $kpisWithDeadlines,
            'analysis_date' => now()->format('Y-m-d'),
        ];
    }

    private function gatherQueryContext(): array
    {
        $summary = $this->dashboard->getExecutiveSummary();

        $directorates = Directorate::active()->get(['id', 'name', 'code'])->toArray();

        $kpis = Kpi::active()->get(['id', 'name', 'code', 'category', 'unit', 'target_value'])->toArray();

        $recentAlerts = Alert::unread()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['type', 'severity', 'title', 'message'])
            ->toArray();

        return [
            'organization_summary' => $summary,
            'directorates' => $directorates,
            'available_kpis' => $kpis,
            'recent_alerts' => $recentAlerts,
            'current_date' => now()->format('Y-m-d'),
        ];
    }

    private function gatherWeeklyContext(): array
    {
        $weekAgo = now()->subWeek();

        // Get entries from this week vs last week
        $thisWeekEntries = KpiEntry::where('period_date', '>=', $weekAgo)
            ->with(['kpi:id,name,category,unit,target_value', 'directorate:id,name'])
            ->get()
            ->groupBy('directorate.name')
            ->map(fn($entries) => $entries->map(fn($e) => [
                'kpi' => $e->kpi?->name,
                'value' => $e->value,
                'change_pct' => $e->getChangePercentage(),
                'status' => $e->kpi ? $e->kpi->getStatusForValue($e->value) : null,
            ]))
            ->toArray();

        $newAlerts = Alert::where('created_at', '>=', $weekAgo)
            ->orderByDesc('created_at')
            ->get(['type', 'severity', 'title', 'message', 'created_at'])
            ->toArray();

        return [
            'week_ending' => now()->format('Y-m-d'),
            'week_starting' => $weekAgo->format('Y-m-d'),
            'directorate_performance' => $thisWeekEntries,
            'alerts_this_week' => $newAlerts,
            'total_alerts' => count($newAlerts),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  Cache Helpers
    // ═══════════════════════════════════════════════════════════

    private function getCache(string $key): ?array
    {
        try {
            return Cache::tags(['ai'])->get($key);
        } catch (\BadMethodCallException) {
            return Cache::get($key);
        }
    }

    private function setCache(string $key, array $value, int $minutes): void
    {
        try {
            Cache::tags(['ai'])->put($key, $value, now()->addMinutes($minutes));
        } catch (\BadMethodCallException) {
            Cache::put($key, $value, now()->addMinutes($minutes));
        }
    }

    /**
     * Clear all AI analysis caches (e.g., after new data import).
     */
    public function clearCache(): void
    {
        try {
            Cache::tags(['ai'])->flush();
        } catch (\BadMethodCallException) {
            // Fall back to individual key clearing
            Cache::forget('ai_executive_insights');
        }
    }
}
