<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Directorate;
use App\Models\Kpi;
use App\Models\KpiEntry;
use App\Models\User;
use App\Mail\KpiAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AlertService
{
    /**
     * Run all alert checks — thresholds, deadlines, stale data.
     */
    public function runAllChecks(): array
    {
        $results = [
            'threshold_alerts' => $this->checkThresholds(),
            'deadline_alerts' => $this->checkDeadlines(),
            'stale_data_alerts' => $this->checkStaleData(),
            'milestone_alerts' => $this->checkMilestones(),
        ];

        $total = array_sum($results);
        Log::info("Alert check complete: {$total} total alerts generated", $results);

        return $results;
    }

    /**
     * Check all KPIs against thresholds and generate alerts.
     */
    public function checkThresholds(): int
    {
        if (!config('dashboard.alerts.enabled', true)) {
            return 0;
        }

        // Eager load directorates with their KPIs to prevent N+1
        $directorates = Directorate::active()->with(['kpis' => fn($q) => $q->where('is_active', true)])->get();
        $alertCount = 0;

        // Pre-load all latest KPI entries for all directorate-KPI combos
        $allKpiIds = $directorates->flatMap(fn($d) => $d->kpis->pluck('id'))->unique()->toArray();
        $allDirIds = $directorates->pluck('id')->toArray();

        $latestEntries = KpiEntry::whereIn('kpi_id', $allKpiIds)
            ->whereIn('directorate_id', $allDirIds)
            ->orderByDesc('period_date')
            ->get()
            ->groupBy(fn($e) => $e->directorate_id . '-' . $e->kpi_id)
            ->map(fn($entries) => $entries->first());

        foreach ($directorates as $directorate) {
            foreach ($directorate->kpis as $kpi) {
                $latest = $latestEntries->get($directorate->id . '-' . $kpi->id);

                if (!$latest) continue;

                $status = $kpi->getStatusForValue($latest->value);

                if ($status === 'critical') {
                    $alert = $this->createAlert(
                        'kpi_threshold',
                        'critical',
                        "{$kpi->name} is critically below threshold",
                        "The KPI '{$kpi->name}' for {$directorate->name} has reached {$kpi->formatValue($latest->value)}, which is below the critical threshold of {$kpi->formatValue($kpi->critical_threshold)}.",
                        $directorate->id,
                        ['kpi_id' => $kpi->id, 'value' => $latest->value, 'threshold' => $kpi->critical_threshold]
                    );
                    $this->sendAlertEmail($alert, $directorate);
                    $alertCount++;
                } elseif ($status === 'warning') {
                    $alert = $this->createAlert(
                        'kpi_threshold',
                        'warning',
                        "{$kpi->name} approaching threshold",
                        "The KPI '{$kpi->name}' for {$directorate->name} is at {$kpi->formatValue($latest->value)}, nearing the warning threshold.",
                        $directorate->id,
                        ['kpi_id' => $kpi->id, 'value' => $latest->value, 'threshold' => $kpi->warning_threshold]
                    );
                    $alertCount++;
                }

                // Check for significant drops
                $changePercent = $latest->getChangePercentage();
                if ($changePercent !== null && $changePercent < -config('dashboard.alerts.kpi_drop_threshold', 10)) {
                    $this->createAlert(
                        'anomaly',
                        'warning',
                        "{$kpi->name} dropped significantly",
                        sprintf(
                            "The KPI '%s' for %s dropped by %.1f%% from %s to %s.",
                            $kpi->name,
                            $directorate->name,
                            abs($changePercent),
                            $kpi->formatValue($latest->previous_value),
                            $kpi->formatValue($latest->value)
                        ),
                        $directorate->id,
                        ['kpi_id' => $kpi->id, 'change' => $changePercent]
                    );
                    $alertCount++;
                }
            }
        }

        return $alertCount;
    }

    /**
     * Check KPIs approaching their deadlines.
     */
    public function checkDeadlines(): int
    {
        $warningDays = config('dashboard.deadlines.warning_days', [14, 7, 3, 1]);
        $alertCount = 0;

        $kpisWithDeadlines = Kpi::active()
            ->whereNotNull('target_deadline')
            ->where('target_deadline', '>=', now())
            ->with('directorates')
            ->get();

        foreach ($kpisWithDeadlines as $kpi) {
            $daysLeft = $kpi->daysUntilDeadline();

            // Only alert on the specific warning days to avoid daily spam
            if (!in_array($daysLeft, $warningDays)) {
                continue;
            }

            foreach ($kpi->directorates as $directorate) {
                // Get the latest entry for this KPI + directorate
                $latest = KpiEntry::where('kpi_id', $kpi->id)
                    ->where('directorate_id', $directorate->id)
                    ->orderByDesc('period_date')
                    ->first();

                $currentValue = $latest?->value ?? 0;
                $targetValue = $directorate->pivot->custom_target ?? $kpi->target_value;

                // Determine if the KPI is on track
                $onTrack = true;
                if ($targetValue && $kpi->trend_direction === 'up_is_good') {
                    $onTrack = $currentValue >= $targetValue;
                } elseif ($targetValue && $kpi->trend_direction === 'down_is_good') {
                    $onTrack = $currentValue <= $targetValue;
                }

                if ($onTrack) continue; // No need to alert if on track

                $severity = match (true) {
                    $daysLeft <= 1 => 'critical',
                    $daysLeft <= 3 => 'critical',
                    $daysLeft <= 7 => 'warning',
                    default => 'info',
                };

                // Avoid duplicate alerts for the same KPI + directorate + day
                $existsToday = Alert::where('type', 'deadline_approaching')
                    ->where('directorate_id', $directorate->id)
                    ->whereDate('created_at', today())
                    ->whereJsonContains('metadata->kpi_id', $kpi->id)
                    ->exists();

                if ($existsToday) continue;

                $alert = $this->createAlert(
                    'deadline_approaching',
                    $severity,
                    "{$kpi->name} deadline in {$daysLeft} day(s)",
                    sprintf(
                        "KPI '%s' for %s has %d day(s) until its deadline (%s). Current value: %s, Target: %s.",
                        $kpi->name,
                        $directorate->name,
                        $daysLeft,
                        $kpi->target_deadline->format('M d, Y'),
                        $kpi->formatValue($currentValue),
                        $targetValue ? $kpi->formatValue($targetValue) : 'not set'
                    ),
                    $directorate->id,
                    [
                        'kpi_id' => $kpi->id,
                        'days_remaining' => $daysLeft,
                        'current_value' => $currentValue,
                        'target_value' => $targetValue,
                        'deadline' => $kpi->target_deadline->format('Y-m-d'),
                    ]
                );

                if ($severity === 'critical') {
                    $this->sendAlertEmail($alert, $directorate);
                }

                $alertCount++;
            }
        }

        // Also check overdue KPIs
        $overdueKpis = Kpi::active()
            ->whereNotNull('target_deadline')
            ->where('target_deadline', '<', now())
            ->with('directorates')
            ->get();

        foreach ($overdueKpis as $kpi) {
            foreach ($kpi->directorates as $directorate) {
                $latest = KpiEntry::where('kpi_id', $kpi->id)
                    ->where('directorate_id', $directorate->id)
                    ->orderByDesc('period_date')
                    ->first();

                $currentValue = $latest?->value ?? 0;
                $targetValue = $directorate->pivot->custom_target ?? $kpi->target_value;

                // Only alert if target not yet met
                $targetMet = false;
                if ($targetValue && $kpi->trend_direction === 'up_is_good') {
                    $targetMet = $currentValue >= $targetValue;
                } elseif ($targetValue && $kpi->trend_direction === 'down_is_good') {
                    $targetMet = $currentValue <= $targetValue;
                }

                if ($targetMet) continue;

                // One overdue alert per week
                $existsThisWeek = Alert::where('type', 'deadline_overdue')
                    ->where('directorate_id', $directorate->id)
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->whereJsonContains('metadata->kpi_id', $kpi->id)
                    ->exists();

                if ($existsThisWeek) continue;

                $alert = $this->createAlert(
                    'deadline_overdue',
                    'critical',
                    "{$kpi->name} deadline OVERDUE",
                    sprintf(
                        "KPI '%s' for %s missed its deadline of %s. Current: %s, Target: %s.",
                        $kpi->name,
                        $directorate->name,
                        $kpi->target_deadline->format('M d, Y'),
                        $kpi->formatValue($currentValue),
                        $targetValue ? $kpi->formatValue($targetValue) : 'not set'
                    ),
                    $directorate->id,
                    [
                        'kpi_id' => $kpi->id,
                        'current_value' => $currentValue,
                        'target_value' => $targetValue,
                        'deadline' => $kpi->target_deadline->format('Y-m-d'),
                        'days_overdue' => abs($kpi->daysUntilDeadline()),
                    ]
                );

                $this->sendAlertEmail($alert, $directorate);
                $alertCount++;
            }
        }

        return $alertCount;
    }

    /**
     * Check for stale data — directorates with no recent KPI entries.
     */
    public function checkStaleData(): int
    {
        $staleDays = config('dashboard.deadlines.stale_data_days', 15);
        $cutoffDate = now()->subDays($staleDays);
        $alertCount = 0;

        $directorates = Directorate::active()->get();

        foreach ($directorates as $directorate) {
            $latestEntry = KpiEntry::where('directorate_id', $directorate->id)
                ->orderByDesc('period_date')
                ->first();

            if (!$latestEntry || $latestEntry->period_date->lt($cutoffDate)) {
                // One stale data alert per week per directorate
                $existsThisWeek = Alert::where('type', 'stale_data')
                    ->where('directorate_id', $directorate->id)
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->exists();

                if ($existsThisWeek) continue;

                $daysSinceUpdate = $latestEntry
                    ? (int) $latestEntry->period_date->diffInDays(now())
                    : null;

                $this->createAlert(
                    'stale_data',
                    'warning',
                    "No recent data for {$directorate->name}",
                    sprintf(
                        "Directorate '%s' has not submitted KPI data in %s. Last update: %s.",
                        $directorate->name,
                        $daysSinceUpdate ? "{$daysSinceUpdate} days" : "never",
                        $latestEntry ? $latestEntry->period_date->format('M d, Y') : 'N/A'
                    ),
                    $directorate->id,
                    [
                        'days_since_update' => $daysSinceUpdate,
                        'last_entry_date' => $latestEntry?->period_date?->format('Y-m-d'),
                    ]
                );
                $alertCount++;
            }
        }

        return $alertCount;
    }

    /**
     * Check for missed KPI milestones.
     */
    public function checkMilestones(): int
    {
        $alertCount = 0;

        $kpisWithMilestones = Kpi::active()
            ->whereNotNull('milestone_targets')
            ->with('directorates')
            ->get();

        foreach ($kpisWithMilestones as $kpi) {
            $milestones = $kpi->milestone_targets;
            if (empty($milestones) || !is_array($milestones)) continue;

            foreach ($kpi->directorates as $directorate) {
                $latest = KpiEntry::where('kpi_id', $kpi->id)
                    ->where('directorate_id', $directorate->id)
                    ->orderByDesc('period_date')
                    ->first();

                if (!$latest) continue;

                $missed = $kpi->isMilestoneMissed($latest->value);
                if (!$missed) continue;

                // One milestone alert per milestone
                $existsForMilestone = Alert::where('type', 'milestone_missed')
                    ->where('directorate_id', $directorate->id)
                    ->whereJsonContains('metadata->kpi_id', $kpi->id)
                    ->whereJsonContains('metadata->milestone_date', $missed['date'])
                    ->exists();

                if ($existsForMilestone) continue;

                $this->createAlert(
                    'milestone_missed',
                    'warning',
                    "{$kpi->name} missed milestone",
                    sprintf(
                        "KPI '%s' for %s missed its %s milestone target of %s. Current value: %s.",
                        $kpi->name,
                        $directorate->name,
                        Carbon::parse($missed['date'])->format('M d, Y'),
                        $kpi->formatValue($missed['target']),
                        $kpi->formatValue($latest->value)
                    ),
                    $directorate->id,
                    [
                        'kpi_id' => $kpi->id,
                        'milestone_date' => $missed['date'],
                        'milestone_target' => $missed['target'],
                        'actual_value' => $latest->value,
                    ]
                );
                $alertCount++;
            }
        }

        return $alertCount;
    }

    /**
     * Create an alert entry.
     */
    public function createAlert(
        string $type,
        string $severity,
        string $title,
        string $message,
        ?int $directorateId = null,
        ?array $metadata = null
    ): Alert {
        return Alert::create([
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'directorate_id' => $directorateId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get unread alerts for display.
     */
    public function getUnreadAlerts(?int $directorateId = null, int $limit = 20): array
    {
        $query = Alert::unread()->orderByDesc('created_at');

        if ($directorateId) {
            $query->where(function ($q) use ($directorateId) {
                $q->where('directorate_id', $directorateId)
                  ->orWhereNull('directorate_id');
            });
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Send an alert email to the relevant directorate head and admins.
     */
    private function sendAlertEmail(Alert $alert, ?Directorate $directorate = null): void
    {
        if (!config('dashboard.alerts.email_notifications', true)) {
            return;
        }

        try {
            $recipients = collect();

            // Add directorate head(s)
            if ($directorate) {
                $dirUsers = User::where('directorate_id', $directorate->id)
                    ->where('is_active', true)
                    ->whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'directorate_head']))
                    ->get();
                $recipients = $recipients->merge($dirUsers);
            }

            // Always include admins for critical alerts
            if ($alert->severity === 'critical') {
                $admins = User::where('is_active', true)
                    ->whereHas('role', fn($q) => $q->where('slug', 'admin'))
                    ->get();
                $recipients = $recipients->merge($admins);
            }

            $recipients = $recipients->unique('id');

            foreach ($recipients as $user) {
                Mail::to($user->email)->queue(new KpiAlertMail($alert, $user));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send alert email', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
