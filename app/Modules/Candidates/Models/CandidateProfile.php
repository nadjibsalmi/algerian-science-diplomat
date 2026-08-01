<?php

namespace App\Modules\Candidates\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CandidateProfile extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id', 'wilaya', 'commune', 'birth_date', 'gender',
        'national_id', 'bio', 'current_institution', 'current_level',
        'current_field', 'current_year', 'linkedin_url', 'researchgate_url',
        'orcid', 'google_scholar_url', 'github_url', 'personal_website',
        'cover_letter_template', 'visibility_settings', 'completeness_pct',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'          => 'date',
            'visibility_settings' => 'array',
            'completeness_pct'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CandidateEducation::class)->orderByDesc('end_year');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CandidateExperience::class)->orderByDesc('end_date');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CandidateLanguage::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(CandidatePublication::class)->orderByDesc('year');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(CandidateAward::class)->orderByDesc('year');
    }

    /** Recalculate and persist the completeness percentage */
    public function recalculateCompleteness(): void
    {
        $fields = [
            'wilaya'             => 5,
            'birth_date'         => 5,
            'bio'                => 5,
            'current_institution'=> 10,
            'current_level'      => 10,
            'current_field'      => 10,
            'cover_letter_template' => 10,
        ];

        $relational = [
            'educations'  => 15,
            'experiences' => 10,
            'languages'   => 10,
            'skills'      => 10,
        ];

        $score = 0;
        foreach ($fields as $field => $weight) {
            if (! empty($this->$field)) {
                $score += $weight;
            }
        }

        foreach ($relational as $relation => $weight) {
            if ($this->$relation()->exists()) {
                $score += $weight;
            }
        }

        $this->update(['completeness_pct' => min(100, $score)]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
