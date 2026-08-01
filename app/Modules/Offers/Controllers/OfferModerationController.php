<?php

namespace App\Modules\Offers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offers\Models\Offer;
use App\Modules\Offers\Requests\ModerateOfferRequest;
use App\Modules\Offers\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OfferModerationController extends Controller
{
    public function __construct(private readonly OfferService $service) {}

    public function index(): JsonResponse
    {
        Gate::authorize('moderateAny', Offer::class);

        return response()->json([
            'offers' => Offer::query()
                ->where('status', 'pending_approval')
                ->with('embassy:id,official_name')
                ->oldest('submitted_at')
                ->paginate(20),
        ]);
    }

    public function decide(ModerateOfferRequest $request, Offer $offer): JsonResponse
    {
        Gate::authorize('moderate', $offer);

        return response()->json([
            'offer' => $this->service->moderate(
                $offer,
                $request->user(),
                $request->validated('decision'),
                $request->validated('notes'),
            ),
        ]);
    }
}