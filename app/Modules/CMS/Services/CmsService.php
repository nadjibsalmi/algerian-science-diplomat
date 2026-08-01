<?php

namespace App\Modules\CMS\Services;

use App\Models\User;
use App\Modules\CMS\Models\BlogPost;
use App\Modules\CMS\Models\GlobalAnnouncement;
use App\Modules\CMS\Models\Page;
use Illuminate\Support\Facades\DB;

class CmsService
{
    public function pages(bool $publishedOnly = false)
    {
        return Page::with('translations')->when($publishedOnly, fn ($q) => $q->where('published', true))->latest()->paginate(20);
    }

    public function page(string $slug, ?string $locale = null): Page
    {
        return Page::where('slug', $slug)->where('published', true)
            ->with(['translations' => fn ($q) => $locale ? $q->where('locale', $locale) : $q])
            ->firstOrFail();
    }

    public function savePage(User $author, array $data, ?Page $page = null): Page
    {
        return DB::transaction(function () use ($author, $data, $page): Page {
            $page ??= new Page();
            $page->fill(collect($data)->only(['slug', 'template', 'published'])->all());
            $page->author_id = $page->author_id ?: $author->id;
            $page->save();
            $page->translations()->delete();
            $page->translations()->createMany($data['translations']);
            return $page->load('translations');
        });
    }

    public function posts(bool $publishedOnly = false)
    {
        return BlogPost::with('translations')->when($publishedOnly, fn ($q) => $q->where('status', 'published'))->latest('published_at')->paginate(20);
    }

    public function post(string $slug, ?string $locale = null): BlogPost
    {
        $post = BlogPost::where('slug', $slug)->where('status', 'published')
            ->with(['translations' => fn ($q) => $locale ? $q->where('locale', $locale) : $q])
            ->firstOrFail();
        $post->increment('view_count');
        return $post;
    }

    public function savePost(User $author, array $data, ?BlogPost $post = null): BlogPost
    {
        return DB::transaction(function () use ($author, $data, $post): BlogPost {
            $post ??= new BlogPost();
            $post->fill(collect($data)->only(['slug', 'cover_image', 'status', 'tags', 'category'])->all());
            $post->author_id = $post->author_id ?: $author->id;
            if ($data['status'] === 'published' && $post->published_at === null) $post->published_at = now();
            $post->save();
            $post->translations()->delete();
            $post->translations()->createMany($data['translations']);
            return $post->load('translations');
        });
    }

    public function announcements(bool $activeOnly = false)
    {
        return GlobalAnnouncement::query()
            ->when($activeOnly, fn ($q) => $q->where('active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now())))
            ->latest()->paginate(20);
    }

    public function saveAnnouncement(array $data, ?GlobalAnnouncement $announcement = null): GlobalAnnouncement
    {
        $announcement ??= new GlobalAnnouncement();
        $announcement->fill($data)->save();
        return $announcement->refresh();
    }
}