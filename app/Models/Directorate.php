<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Directorate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'head_name',
        'head_email',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $directorate) {
            if (empty($directorate->slug)) {
                $directorate->slug = Str::slug($directorate->name);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kpis(): BelongsToMany
    {
        return $this->belongsToMany(Kpi::class, 'directorate_kpi')
            ->withPivot('custom_target')
            ->withTimestamps();
    }

    public function kpiEntries(): HasMany
    {
        return $this->hasMany(KpiEntry::class);
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function simulationLogs(): HasMany
    {
        return $this->hasMany(SimulationLog::class);
    }

    public function executiveNotes(): HasMany
    {
        return $this->hasMany(ExecutiveNote::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ── Computed Attributes ────────────────────────────────

    public function getLatestKpiSummary(): array
    {
        $entries = $this->kpiEntries()
            ->with('kpi')
            ->where('period_date', '>=', now()->subMonths(1))
            ->orderByDesc('period_date')
            ->get()
            ->groupBy('kpi_id');

        $summary = [];
        foreach ($entries as $kpiId => $kpiEntries) {
            $latest = $kpiEntries->first();
            $previous = $kpiEntries->skip(1)->first();
            $summary[] = [
                'kpi' => $latest->kpi->name,
                'value' => $latest->value,
                'previous' => $previous?->value,
                'change' => $previous ? round((($latest->value - $previous->value) / max($previous->value, 1)) * 100, 2) : null,
                'unit' => $latest->kpi->unit,
            ];
        }

        return $summary;
    }
}
