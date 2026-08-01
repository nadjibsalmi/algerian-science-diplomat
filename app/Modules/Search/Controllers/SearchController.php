<?php

namespace App\Modules\Search\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Search\Requests\SearchRequest;
use App\Modules\Search\Services\SearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $service) {}

    public function offers(SearchRequest $request): JsonResponse
    {
        return response()->json(['results' => $this->service->offers($request->validated(), $request->user())]);
    }
}