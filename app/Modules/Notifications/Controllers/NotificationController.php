<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Models\SearchAlert;
use App\Modules\Notifications\Requests\StoreSearchAlertRequest;
use App\Modules\Notifications\Requests\UpdatePreferenceRequest;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'notifications' => $this->service->notifications($request->user()),
            'unread_count' => $this->service->unreadCount($request->user()),
        ]);
    }

    public function markRead(Request $request, ?string $notification = null): JsonResponse
    {
        return response()->json(['marked' => $this->service->markRead($request->user(), $notification)]);
    }

    public function destroy(Request $request, string $notification): JsonResponse
    {
        $this->service->delete($request->user(), $notification);

        return response()->json(['message' => 'Notification supprimée.']);
    }

    public function preferences(Request $request): JsonResponse
    {
        return response()->json(['preferences' => $this->service->preferences($request->user())]);
    }

    public function updatePreference(UpdatePreferenceRequest $request): JsonResponse
    {
        return response()->json(['preference' => $this->service->upsertPreference($request->user(), $request->validated())]);
    }

    public function alerts(Request $request): JsonResponse
    {
        return response()->json(['alerts' => $this->service->alerts($request->user())]);
    }

    public function storeAlert(StoreSearchAlertRequest $request): JsonResponse
    {
        return response()->json(['alert' => $this->service->createAlert($request->user(), $request->validated())], 201);
    }

    public function updateAlert(StoreSearchAlertRequest $request, SearchAlert $alert): JsonResponse
    {
        return response()->json(['alert' => $this->service->updateAlert($request->user(), $alert, $request->validated())]);
    }

    public function destroyAlert(Request $request, SearchAlert $alert): JsonResponse
    {
        $this->service->deleteAlert($request->user(), $alert);

        return response()->json(['message' => 'Alerte supprimée.']);
    }

    public function digest(Request $request): JsonResponse
    {
        return response()->json(['digest' => $this->service->digest($request->user())]);
    }

    public function updateDigest(Request $request): JsonResponse
    {
        $data = $request->validate(['frequency' => ['required', 'in:immediate,daily,weekly']]);

        return response()->json(['digest' => $this->service->updateDigest($request->user(), $data['frequency'])]);
    }
}