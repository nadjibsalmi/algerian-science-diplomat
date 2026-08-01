<?php

namespace App\Modules\Candidates\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateEducation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'candidate_profile_id', 'institution', 'degree', 'field',
        'grade', 'start_year', 'end_year', 'current', 'description',
    ];

    protected function casts(): array
    {
        return ['current' => 'boolean'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id');
    }
}
