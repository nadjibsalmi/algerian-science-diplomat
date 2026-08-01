<?php

namespace App\Modules\Messaging\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Messaging\Events\MessageSent;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Requests\SendMessageRequest;
use App\Modules\Messaging\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function __construct(private readonly MessagingService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['conversations' => $this->service->conversations($request->user())]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);
        $this->service->markRead($request->user(), $conversation);

        return response()->json(['conversation' => $this->service->conversation($request->user(), $conversation)]);
    }

    public function sendMessage(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('send', $conversation);
        $message = $this->service->send(
            $request->user(),
            $conversation,
            $request->validated('message'),
            $request->validated('attachment_ids', []),
        );

        broadcast(new MessageSent($message, $conversation))->toOthers();

        return response()->json(['message' => $message], 201);
    }

    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        return response()->json(['marked' => $this->service->markRead($request->user(), $conversation)]);
    }

    public function archive(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        return response()->json(['conversation' => $this->service->archive($request->user(), $conversation)]);
    }
}
