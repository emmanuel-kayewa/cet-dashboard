<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpProject extends Model
{
    protected $fillable = [
        'project_code',
        'project_name',
        'sector',
        'sub_sector',
        'status',
        'programme',
        'province',
        'district',
        'contractor',
        'developer',
        'funder',
        'funding_type',
        'cost_usd',
        'cost_zmw',
        'capacity_mw',
        'progress_pct',
        'cod_planned',
        'key_issue_summary',
        'last_update_date',
        'rag_status',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'cost_usd'         => 'decimal:2',
        'cost_zmw'         => 'decimal:2',
        'capacity_mw'      => 'decimal:3',
        'progress_pct'     => 'decimal:2',
        'cod_planned'      => 'date',
        'last_update_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(PpMilestone::class);
    }

    public function financials(): HasMany
    {
        return $this->hasMany(PpFinancial::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(PpRisk::class);
    }

    public function safeguards(): HasMany
    {
        return $this->hasMany(PpSafeguard::class);
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeBySector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRag($query, string $rag)
    {
        return $query->where('rag_status', $rag);
    }
}
