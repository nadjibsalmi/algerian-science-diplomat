<?php

namespace App\Modules\Applications\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'application_id', 'from_status', 'to_status', 'changed_by_user_id', 'note',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
