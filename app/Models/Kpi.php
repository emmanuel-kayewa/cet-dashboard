<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'category',
        'unit',
        'currency_code',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'target_deadline',
        'target_period_type',
        'milestone_targets',
        'trend_direction',
        'is_global',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'warning_threshold' => 'decimal:2',
        'critical_threshold' => 'decimal:2',
        'target_deadline' => 'date',
        'milestone_targets' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────

    public function directorates(): BelongsToMany
    {
        return $this->belongsToMany(Directorate::class, 'directorate_kpi')
            ->withPivot('custom_target')
            ->withTimestamps();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(KpiEntry::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Helpers ────────────────────────────────────────────

    public function getStatusForValue(float $value): string
    {
        if ($this->critical_threshold !== null && $this->isBelow($value, $this->critical_threshold)) {
            return 'critical';
        }
        if ($this->warning_threshold !== null && $this->isBelow($value, $this->warning_threshold)) {
            return 'warning';
        }
        return 'healthy';
    }

    private function isBelow(float $value, float $threshold): bool
    {
        if ($this->trend_direction === 'up_is_good') {
            return $value < $threshold;
        }
        if ($this->trend_direction === 'down_is_good') {
            return $value > $threshold;
        }
        return false;
    }

    public function formatValue(float $value): string
    {
        return match ($this->unit) {
            'percentage' => number_format($value, 1) . '%',
            'currency' => ($this->currency_code ?? 'ZMW') . ' ' . number_format($value, 2),
            'ratio' => number_format($value, 2),
            default => number_format($value, 0),
        };
    }

    // ── Deadline Helpers ───────────────────────────────────

    /**
     * Check whether this KPI has a deadline set.
     */
    public function hasDeadline(): bool
    {
        return $this->target_deadline !== null;
    }

    /**
     * Get the number of days until the deadline (negative = overdue).
     */
    public function daysUntilDeadline(): ?int
    {
        if (!$this->hasDeadline()) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->target_deadline, false);
    }

    /**
     * Check if the deadline is approaching within the given number of days.
     */
    public function isApproachingDeadline(int $withinDays = 14): bool
    {
        $days = $this->daysUntilDeadline();
        return $days !== null && $days >= 0 && $days <= $withinDays;
    }

    /**
     * Check if the deadline has passed.
     */
    public function isOverdue(): bool
    {
        $days = $this->daysUntilDeadline();
        return $days !== null && $days < 0;
    }

    /**
     * Get the current milestone target (the next upcoming milestone).
     */
    public function getCurrentMilestone(): ?array
    {
        if (empty($this->milestone_targets)) {
            return null;
        }

        $now = now();
        foreach ($this->milestone_targets as $milestone) {
            $date = Carbon::parse($milestone['date']);
            if ($date->isFuture() || $date->isToday()) {
                return $milestone;
            }
        }

        // All milestones passed — return the last one
        return end($this->milestone_targets) ?: null;
    }

    /**
     * Get the most recently passed milestone.
     */
    public function getLastPassedMilestone(): ?array
    {
        if (empty($this->milestone_targets)) {
            return null;
        }

        $passed = null;
        foreach ($this->milestone_targets as $milestone) {
            $date = Carbon::parse($milestone['date']);
            if ($date->isPast()) {
                $passed = $milestone;
            }
        }

        return $passed;
    }

    /**
     * Check whether a milestone was missed (value didn't meet target by date).
     */
    public function isMilestoneMissed(float $currentValue): ?array
    {
        $lastPassed = $this->getLastPassedMilestone();
        if (!$lastPassed) {
            return null;
        }

        $milestoneTarget = (float) $lastPassed['target'];

        if ($this->trend_direction === 'up_is_good' && $currentValue < $milestoneTarget) {
            return $lastPassed;
        }

        if ($this->trend_direction === 'down_is_good' && $currentValue > $milestoneTarget) {
            return $lastPassed;
        }

        return null;
    }
}
