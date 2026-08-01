<?php

namespace App\Modules\Administration\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSuspension extends Model
{
    use HasUuids;
    protected $fillable = ['user_id', 'suspended_by', 'reason', 'suspended_until', 'lifted_at', 'lifted_by'];
    protected function casts(): array { return ['suspended_until' => 'datetime', 'lifted_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function suspender(): BelongsTo { return $this->belongsTo(User::class, 'suspended_by'); }
    public function liftedBy(): BelongsTo { return $this->belongsTo(User::class, 'lifted_by'); }
}