<?php

namespace App\Modules\Applications\Models;

use App\Models\User;
use App\Modules\Documents\Models\Document;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Offers\Models\Offer;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Application extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    public const STATUSES = [
        'submitted', 'processing', 'shortlisted',
        'interview', 'accepted', 'rejected', 'waitlisted', 'withdrawn',
    ];

    protected $fillable = [
        'offer_id', 'user_id', 'status', 'cover_letter', 'cover_letter_file',
        'submitted_at', 'withdrawn_at', 'eligibility_passed', 'eligibility_details', 'answers',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at'        => 'datetime',
            'withdrawn_at'        => 'datetime',
            'eligibility_passed'  => 'boolean',
            'eligibility_details' => 'array',
            'answers'             => 'array',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('created_at');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(ApplicationEvaluation::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'application_documents')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function transitionStatus(string $newStatus, User $changedBy, ?string $note = null): void
    {
        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        ApplicationStatusHistory::create([
            'application_id'    => $this->id,
            'from_status'       => $oldStatus,
            'to_status'         => $newStatus,
            'changed_by_user_id'=> $changedBy->id,
            'note'              => $note,
        ]);

        activity()->causedBy($changedBy)->performedOn($this)
            ->withProperties(['from' => $oldStatus, 'to' => $newStatus])
            ->log('Application status changed');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
