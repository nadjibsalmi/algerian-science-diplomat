<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostTranslation extends Model
{
    use HasUuids;
    protected $fillable = ['blog_post_id', 'locale', 'title', 'content', 'excerpt', 'meta_title', 'meta_description'];
    protected function casts(): array { return ['content' => 'array']; }
    public function post(): BelongsTo { return $this->belongsTo(BlogPost::class, 'blog_post_id'); }
}