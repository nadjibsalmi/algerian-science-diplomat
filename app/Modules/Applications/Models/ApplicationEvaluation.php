<?php

namespace App\Modules\Applications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEvaluation extends Model
{
    use HasUuids;

    protected $fillable = [
        'application_id', 'evaluator_id', 'score', 'comment', 'criteria_scores',
    ];

    protected function casts(): array
    {
        return ['criteria_scores' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
