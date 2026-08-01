<?php

namespace App\Modules\CMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['slug', 'cover_image', 'status', 'author_id', 'tags', 'category', 'published_at'];
    protected function casts(): array { return ['tags' => 'array', 'published_at' => 'datetime']; }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function translations(): HasMany { return $this->hasMany(BlogPostTranslation::class); }
}