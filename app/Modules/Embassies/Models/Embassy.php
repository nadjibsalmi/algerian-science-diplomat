<?php

namespace App\Modules\Embassies\Models;

use App\Models\User;
use App\Modules\Offers\Models\Offer;
use Database\Factories\EmbassyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Embassy extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    /**
     * AUDIT FIX: Laravel's HasFactory auto-resolves a factory class name
     * by mirroring the model's own namespace under Database\Factories
     * (e.g. App\Models\Foo -> Database\Factories\FooFactory works, but
     * App\Modules\Embassies\Models\Embassy would guess
     * Database\Factories\Modules\Embassies\Models\EmbassyFactory, which
     * doesn't exist - confirmed live via CI: "Class ... not found").
     * Every model under this project's app/Modules/*\/Models/ structure
     * needs this same explicit override, since the convention can never
     * match a modular namespace like this by design.
     */
    protected static function newFactory(): Factory
    {
        return EmbassyFactory::new();
    }

    protected $fillable = [
        'country', 'official_name', 'logo', 'email', 'phone',
        'website', 'address', 'verified', 'status',
    ];

    protected function casts(): array
    {
        return ['verified' => 'boolean'];
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Users with any role (director/recruiter/hr) at this embassy.
     * This membership table is the single source of truth tenant
     * isolation is enforced against - see OfferPolicy.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'embassy_user')
            ->withPivot('role_in_embassy')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EmbassyInvitation::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
