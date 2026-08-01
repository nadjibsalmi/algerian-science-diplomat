<?php

namespace App\Modules\SearchAlerts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SearchAlerts\Models\SearchAlert;
use App\Modules\SearchAlerts\Requests\SearchAlertRequest;
use App\Modules\SearchAlerts\Services\SearchAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SearchAlertController extends Controller
{
    public function __construct(private readonly SearchAlertService $service) {}
    public function index(Request $request): JsonResponse { return response()->json(['alerts' => $this->service->list($request->user())]); }
    public function store(SearchAlertRequest $request): JsonResponse { return response()->json(['alert' => $this->service->create($request->user(), $request->validated())], 201); }
    public function update(SearchAlertRequest $request, SearchAlert $alert): JsonResponse { Gate::authorize('update', $alert); return response()->json(['alert' => $this->service->update($request->user(), $alert, $request->validated())]); }
    public function destroy(Request $request, SearchAlert $alert): JsonResponse { Gate::authorize('delete', $alert); $this->service->delete($request->user(), $alert); return response()->json(['message' => 'Alerte supprimée.']); }
}