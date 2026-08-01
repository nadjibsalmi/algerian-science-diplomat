<?php

namespace App\Modules\Candidates\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Candidates\Models\CandidateProfile;
use App\Modules\Candidates\Requests\CandidateEntryRequest;
use App\Modules\Candidates\Requests\FavoriteOfferRequest;
use App\Modules\Candidates\Requests\UpdateCandidateProfileRequest;
use App\Modules\Candidates\Services\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CandidateController extends Controller
{
    public function __construct(private readonly CandidateService $service) {}

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json($this->service->dashboard($request->user()));
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = $this->service->profile($request->user())->load([
            'educations', 'experiences', 'languages', 'skills', 'awards', 'publications',
        ]);
        Gate::authorize('view', $profile);

        return response()->json(['profile' => $profile]);
    }

    public function updateProfile(UpdateCandidateProfileRequest $request): JsonResponse
    {
        $profile = $this->service->profile($request->user());
        Gate::authorize('update', $profile);

        return response()->json([
            'profile' => $this->service->updateProfile($request->user(), $request->validated()),
        ]);
    }

    public function entries(Request $request, string $section): JsonResponse
    {
        return response()->json(['entries' => $this->service->entries($request->user(), $section)]);
    }

    public function storeEntry(CandidateEntryRequest $request, string $section): JsonResponse
    {
        return response()->json([
            'entry' => $this->service->createEntry($request->user(), $section, $request->validated()),
        ], 201);
    }

    public function updateEntry(CandidateEntryRequest $request, string $section, string $entry): JsonResponse
    {
        return response()->json([
            'entry' => $this->service->updateEntry($request->user(), $section, $entry, $request->validated()),
        ]);
    }

    public function deleteEntry(Request $request, string $section, string $entry): JsonResponse
    {
        $this->service->deleteEntry($request->user(), $section, $entry);

        return response()->json(['message' => 'Élément supprimé.']);
    }

    public function favorites(Request $request): JsonResponse
    {
        return response()->json(['favorites' => $this->service->favorites($request->user())]);
    }

    public function toggleFavorite(FavoriteOfferRequest $request): JsonResponse
    {
        $favorited = $this->service->toggleFavorite($request->user(), $request->validated('offer_id'));

        return response()->json(['offer_id' => $request->validated('offer_id'), 'favorited' => $favorited]);
    }
}