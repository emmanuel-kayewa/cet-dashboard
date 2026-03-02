<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risk extends Model
{
    use HasFactory;

    protected $fillable = [
        'directorate_id',
        'title',
        'description',
        'category',
        'likelihood',
        'impact',
        'status',
        'mitigation_plan',
        'owner',
        'review_date',
        'source',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function getRiskLevel(): string
    {
        $score = $this->likelihood * $this->impact;
        return match (true) {
            $score >= 20 => 'critical',
            $score >= 12 => 'high',
            $score >= 6 => 'medium',
            default => 'low',
        };
    }

    public function getRiskColor(): string
    {
        return match ($this->getRiskLevel()) {
            'critical' => '#dc2626',
            'high' => '#f97316',
            'medium' => '#eab308',
            'low' => '#22c55e',
        };
    }

    public function isOverdueForReview(): bool
    {
        return $this->review_date && $this->review_date->isPast();
    }

    public function scopeHighRisk($query)
    {
        return $query->whereRaw('likelihood * impact >= 12');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
