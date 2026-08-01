<?php

namespace App\Modules\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasUuids;
    protected $fillable = ['user_id', 'event', 'subject_type', 'subject_id', 'properties', 'ip_hash'];
    protected function casts(): array { return ['properties' => 'array']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}