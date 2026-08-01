<?php

namespace App\Modules\Embassies\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Embassies\Requests\UpdateEmbassyRequest;
use App\Modules\Embassies\Services\EmbassyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmbassyController extends Controller
{
    public function __construct(private readonly EmbassyService $service) {}

    public function dashboard(Request $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('viewDashboard', $embassy);

        return response()->json($this->service->dashboard($embassy));
    }

    public function show(Request $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('viewDashboard', $embassy);

        return response()->json(['embassy' => $this->service->dashboard($embassy)['embassy']]);
    }

    public function update(UpdateEmbassyRequest $request): JsonResponse
    {
        $embassy = $this->resolveEmbassy($request);
        $this->authorize('update', $embassy);

        return response()->json([
            'embassy' => $this->service->update($embassy, $request->validated()),
        ]);
    }

    private function resolveEmbassy(Request $request): Embassy
    {
        if ($request->filled('embassy_id')) {
            return Embassy::query()->findOrFail($request->string('embassy_id')->toString());
        }

        $embassyId = $request->route('embassy');

        if ($embassyId instanceof Embassy) {
            return $embassyId;
        }

        return $request->user()->embassies()->firstOrFail();
    }
}