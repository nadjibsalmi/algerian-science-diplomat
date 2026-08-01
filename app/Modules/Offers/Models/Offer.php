<?php

namespace App\Modules\Offers\Models;

use App\Modules\Embassies\Models\Embassy;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Offer extends Model
{
    use HasFactory, HasUuids, LogsActivity, Searchable, SoftDeletes;

    /** Same structural reason as Embassy::newFactory() - see that model for the full explanation. */
    protected static function newFactory(): Factory
    {
        return OfferFactory::new();
    }

    protected $fillable = [
        'embassy_id', 'title', 'slug', 'description', 'country', 'city',
        'offer_type', 'category', 'research_field', 'level', 'contract_type',
        'salary', 'currency', 'deadline', 'status', 'visibility',
        'published_at', 'closed_at', 'submitted_by', 'moderated_by',
        'moderation_status', 'moderation_notes', 'submitted_at',
        'duplicated_from_id',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'salary' => 'decimal:2',
        ];
    }

    public function embassy(): BelongsTo
    {
        return $this->belongsTo(Embassy::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'moderated_by');
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicated_from_id');
    }

    /**
     * AUDIT-CRITICAL scope: this is the query-level enforcement of the
     * SRS's explicit multi-tenant requirement ("une ambassade ne peut
     * jamais voir les offres d'une autre"). Every controller listing
     * offers for an embassy dashboard MUST go through this scope (or the
     * equivalent OfferPolicy check for single-record access) rather than
     * trusting a client-supplied embassy_id filter directly.
     */
    public function scopeForEmbassy(Builder $query, string $embassyId): Builder
    {
        return $query->where('embassy_id', $embassyId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('visibility', 'public')
            ->where(fn ($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('deadline')
            ->where('deadline', '<=', now());
    }

    /** Laravel Scout / Meilisearch indexed fields (Search module requirement). */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'country' => $this->country,
            'city' => $this->city,
            'offer_type' => $this->offer_type,
            'category' => $this->category,
            'research_field' => $this->research_field,
            'status' => $this->status,
            'visibility' => $this->visibility,
        ];
    }

    /** Only index offers that are actually publicly visible - never leak drafts/paused offers into the public search index. */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && $this->visibility === 'public';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
