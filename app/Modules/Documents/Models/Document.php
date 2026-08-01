<?php

namespace App\Modules\Documents\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\Applications\Models\Application;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    public const TYPES = [
        'diploma', 'transcript', 'cv', 'recommendation', 'passport',
        'birth_cert', 'address_proof', 'publication', 'cover_letter',
        'work_cert', 'id_photo', 'other',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SCANNING  = 'scanning';
    public const STATUS_CLEAN     = 'clean';
    public const STATUS_INFECTED  = 'infected';
    public const STATUS_REJECTED  = 'rejected';

    protected $fillable = [
        'user_id', 'type', 'name', 'original_filename', 'path', 'disk',
        'mime_type', 'size_bytes', 'status', 'virus_scan_result',
        'virus_scanned_at', 'parent_document_id', 'version',
        'expires_at', 'share_token', 'share_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'virus_scanned_at'       => 'datetime',
            'expires_at'             => 'date',
            'share_token_expires_at' => 'datetime',
            'size_bytes'             => 'integer',
            'version'                => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'application_documents')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Generate a presigned temporary URL for secure in-app viewing */
    public function temporaryUrl(int $minutesTtl = 30): string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addMinutes($minutesTtl));
    }

    /** Generate a 24h share token and persist it */
    public function generateShareToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'share_token'            => hash('sha256', $token),
            'share_token_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at !== null && $this->expires_at->diffInDays(now()) <= $days;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'type', 'name'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
