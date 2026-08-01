<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GlobalAnnouncement extends Model
{
    use HasUuids;
    protected $fillable = ['type', 'message', 'link', 'active', 'starts_at', 'ends_at'];
    protected function casts(): array { return ['message' => 'array', 'active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime']; }
}