<?php

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value', 'group'];
    protected function casts(): array { return ['value' => 'array']; }
}