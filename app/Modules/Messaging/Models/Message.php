<?php

namespace App\Modules\Messaging\Models;

use App\Models\User;
use App\Modules\Documents\Models\Document;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'type', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'message_attachments')->withTimestamps();
    }

    public function markReadBy(string $userId): void
    {
        MessageRead::firstOrCreate([
            'message_id' => $this->id,
            'user_id'    => $userId,
        ], ['read_at' => now()]);
    }
}
