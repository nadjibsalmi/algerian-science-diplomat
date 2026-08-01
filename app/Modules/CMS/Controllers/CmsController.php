<?php

namespace App\Modules\CMS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\BlogPost;
use App\Modules\CMS\Models\GlobalAnnouncement;
use App\Modules\CMS\Models\Page;
use App\Modules\CMS\Requests\AnnouncementRequest;
use App\Modules\CMS\Requests\BlogPostRequest;
use App\Modules\CMS\Requests\PageRequest;
use App\Modules\CMS\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function __construct(private readonly CmsService $service) {}
    public function pages(): JsonResponse { return response()->json(['pages' => $this->service->pages(true)]); }
    public function page(Request $request, string $slug): JsonResponse { return response()->json(['page' => $this->service->page($slug, $request->string('locale')->toString() ?: null)]); }
    public function savePage(PageRequest $request, ?Page $page = null): JsonResponse { $this->authorizeCms($request); return response()->json(['page' => $this->service->savePage($request->user(), $request->validated(), $page)]); }
    public function posts(): JsonResponse { return response()->json(['posts' => $this->service->posts(true)]); }
    public function post(Request $request, string $slug): JsonResponse { return response()->json(['post' => $this->service->post($slug, $request->string('locale')->toString() ?: null)]); }
    public function savePost(BlogPostRequest $request, ?BlogPost $post = null): JsonResponse { $this->authorizeCms($request); return response()->json(['post' => $this->service->savePost($request->user(), $request->validated(), $post)]); }
    public function announcements(): JsonResponse { return response()->json(['announcements' => $this->service->announcements(true)]); }
    public function saveAnnouncement(AnnouncementRequest $request, ?GlobalAnnouncement $announcement = null): JsonResponse { $this->authorizeCms($request); return response()->json(['announcement' => $this->service->saveAnnouncement($request->validated(), $announcement)]); }

    private function authorizeCms(Request $request): void
    {
        abort_unless(
            $request->user()->hasAnyRole(['Super Admin', 'Platform Admin'])
                || $request->user()->can('manage_cms'),
            403,
        );
    }
}