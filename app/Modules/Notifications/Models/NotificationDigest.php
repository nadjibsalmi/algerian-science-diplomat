<?php

namespace App\Modules\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDigest extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'frequency'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}