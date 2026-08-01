<?php

namespace App\Modules\CMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['slug', 'template', 'published', 'author_id'];

    protected function casts(): array
    {
        return ['published' => 'boolean'];
    }

    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function translations(): HasMany { return $this->hasMany(PageTranslation::class); }
}