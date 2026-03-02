<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveNote extends Model
{
    protected $fillable = [
        'user_id',
        'directorate_id',
        'title',
        'content',
        'is_pinned',
        'visibility',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeVisible($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'all')
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('visibility', 'directorate')
                     ->where('directorate_id', $user->directorate_id);
              })
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('visibility', 'private')
                     ->where('user_id', $user->id);
              });
        });
    }
}
