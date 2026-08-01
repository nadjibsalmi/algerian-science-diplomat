<?php

namespace App\Modules\Analytics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Requests\RecordEventRequest;
use App\Modules\Analytics\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $service) {}
    public function record(RecordEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        return response()->json(['event' => $this->service->record($request->user(), $data['event'], $data['properties'] ?? [], $data['subject_type'] ?? null, $data['subject_id'] ?? null, $request->ip())], 201);
    }
    public function dashboard(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can('view_analytics'), 403);
        return response()->json(['analytics' => $this->service->dashboard($request->string('from')->toString() ?: null, $request->string('to')->toString() ?: null)]);
    }
}