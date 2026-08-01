<?php

namespace App\Models;

use App\Modules\Embassies\Models\Embassy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * Authentication-only user record, per the SRS data model.
 *
 * Deliberately minimal: personal/professional profile data belongs to the
 * Candidates module (candidate_profiles table), not here - this table
 * exists purely for authentication, authorization (roles/permissions via
 * Spatie), and account-level metadata (2FA, status, last login).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'phone',
        'avatar',
        'preferred_language',
        'timezone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            '2fa_enabled' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * Embassies this user is a real, persisted member of (director,
     * recruiter, or hr) - the single source of truth OfferPolicy (and
     * every other tenant-scoped policy) checks against for multi-tenant
     * isolation.
     */
    public function embassies(): BelongsToMany
    {
        return $this->belongsToMany(Embassy::class, 'embassy_user')
            ->withPivot('role_in_embassy')
            ->withTimestamps();
    }

    /**
     * What gets audit-logged (Audit module requirement: "Journalisation
     * complète"). Password/tokens are deliberately excluded even from the
     * audit trail itself.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['firstname', 'lastname', 'email', 'status', '2fa_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
