<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'azure_id',
        'avatar',
        'job_title',
        'department',
        'role_id',
        'directorate_id',
        'magic_link_token',
        'magic_link_expires_at',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'preferences',
    ];

    protected $hidden = [
        'magic_link_token',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'magic_link_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'preferences' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function kpiEntries(): HasMany
    {
        return $this->hasMany(KpiEntry::class, 'entered_by');
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class, 'entered_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function executiveNotes(): HasMany
    {
        return $this->hasMany(ExecutiveNote::class);
    }

    // ── Role Helpers ───────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
    }

    public function isDirectorateHead(): bool
    {
        return $this->role?->name === Role::DIRECTORATE_HEAD;
    }

    public function isExecutive(): bool
    {
        return $this->role?->name === Role::EXECUTIVE;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    public function canViewDirectorate(int $directorateId): bool
    {
        if ($this->isAdmin() || $this->isExecutive()) {
            return true;
        }
        return $this->directorate_id === $directorateId;
    }

    public function canInputData(): bool
    {
        return $this->isAdmin() || $this->isDirectorateHead();
    }

    // ── Magic Link ─────────────────────────────────────────

    public function generateMagicLink(): string
    {
        $token = Str::random(64);
        $this->update([
            'magic_link_token' => hash('sha256', $token),
            'magic_link_expires_at' => now()->addMinutes(15),
        ]);
        return $token;
    }

    public function validateMagicLink(string $token): bool
    {
        return $this->magic_link_token === hash('sha256', $token)
            && $this->magic_link_expires_at?->isFuture();
    }

    public function clearMagicLink(): void
    {
        $this->update([
            'magic_link_token' => null,
            'magic_link_expires_at' => null,
        ]);
    }

    // ── Preferences ────────────────────────────────────────

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, mixed $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }
}
