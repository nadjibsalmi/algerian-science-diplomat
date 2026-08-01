<?php

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $table = 'activity_log';
    protected $guarded = [];
    protected function casts(): array { return ['properties' => 'array']; }
}