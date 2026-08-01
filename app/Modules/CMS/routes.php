<?php

use App\Modules\CMS\Controllers\CmsController;
use Illuminate\Support\Facades\Route;

Route::get('/content/pages', [CmsController::class, 'pages'])->name('cms.pages');
Route::get('/content/pages/{slug}', [CmsController::class, 'page'])->name('cms.page');
Route::get('/content/posts', [CmsController::class, 'posts'])->name('cms.posts');
Route::get('/content/posts/{slug}', [CmsController::class, 'post'])->name('cms.post');
Route::get('/content/announcements', [CmsController::class, 'announcements'])->name('cms.announcements');
Route::middleware('auth')->prefix('admin/cms')->name('admin.cms.')->group(function (): void {
    Route::post('/pages', [CmsController::class, 'savePage'])->name('pages.store');
    Route::put('/pages/{page}', [CmsController::class, 'savePage'])->name('pages.update');
    Route::post('/posts', [CmsController::class, 'savePost'])->name('posts.store');
    Route::put('/posts/{post}', [CmsController::class, 'savePost'])->name('posts.update');
    Route::post('/announcements', [CmsController::class, 'saveAnnouncement'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [CmsController::class, 'saveAnnouncement'])->name('announcements.update');
});