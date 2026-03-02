<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'directorate_id',
        'name',
        'description',
        'status',
        'budget',
        'spent',
        'completion_percentage',
        'start_date',
        'end_date',
        'actual_end_date',
        'priority',
        'project_manager',
        'milestones',
        'source',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
        'milestones' => 'array',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function isOverBudget(): bool
    {
        return $this->budget && $this->spent > $this->budget;
    }

    public function isOverdue(): bool
    {
        return $this->end_date && $this->end_date->isPast() && $this->status !== 'completed';
    }

    public function getBudgetUtilization(): ?float
    {
        if (!$this->budget || $this->budget == 0) {
            return null;
        }
        return round(($this->spent / $this->budget) * 100, 2);
    }

    public function getDaysRemaining(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'in_progress']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
