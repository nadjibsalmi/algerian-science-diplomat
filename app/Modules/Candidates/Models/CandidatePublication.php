<?php

namespace App\Modules\Candidates\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePublication extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['candidate_profile_id', 'title', 'journal', 'year', 'doi', 'url', 'type'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id');
    }
}
