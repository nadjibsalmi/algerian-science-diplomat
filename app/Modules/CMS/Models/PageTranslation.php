<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    use HasUuids;

    protected $fillable = ['page_id', 'locale', 'title', 'content', 'meta_title', 'meta_description', 'og_image'];
    protected function casts(): array { return ['content' => 'array']; }
    public function page(): BelongsTo { return $this->belongsTo(Page::class); }
}