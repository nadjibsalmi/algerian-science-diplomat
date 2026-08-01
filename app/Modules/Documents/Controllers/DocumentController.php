<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Requests\ShareDocumentRequest;
use App\Modules\Documents\Requests\UploadDocumentRequest;
use App\Modules\Documents\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Document::class);

        return response()->json([
            'documents' => Document::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(30),
        ]);
    }

    public function store(UploadDocumentRequest $request): JsonResponse
    {
        Gate::authorize('upload', Document::class);

        return response()->json([
            'document' => $this->service->upload($request->user(), $request->file('file'), $request->validated()),
        ], 201);
    }

    public function show(Document $document): JsonResponse
    {
        Gate::authorize('view', $document);

        return response()->json(['document' => $document->load('applications:id')]);
    }

    public function download(Document $document)
    {
        Gate::authorize('download', $document);

        return $this->service->download($document);
    }

    public function share(ShareDocumentRequest $request, Document $document): JsonResponse
    {
        Gate::authorize('share', $document);
        $token = $this->service->share($request->user(), $document);

        return response()->json([
            'url' => route('documents.shared', ['token' => $token]),
            'expires_at' => $document->fresh()->share_token_expires_at,
        ]);
    }

    public function shared(string $token)
    {
        $document = $this->service->resolveShareToken($token);

        return $this->service->download($document);
    }

    public function destroy(Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);
        $this->service->delete(request()->user(), $document);

        return response()->json(['message' => 'Document supprimé.']);
    }
}