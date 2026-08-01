<?php

namespace App\Modules\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAlert extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'name', 'filters', 'active', 'last_sent_at'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'active' => 'boolean', 'last_sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}