<?php

namespace App\Modules\Offers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offers\Models\Offer;
use App\Modules\Offers\Requests\StoreOfferRequest;
use App\Modules\Offers\Requests\UpdateOfferRequest;
use App\Modules\Offers\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $service) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Offer::class);
        $query = Offer::query()->with('embassy:id,official_name');
        $user = $request->user();

        if ($user !== null && ! $user->hasRole('Super Admin') && ! $user->hasRole('Platform Admin')) {
            $query->whereIn('embassy_id', $user->embassies()->select('embassies.id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        Gate::authorize('create', Offer::class);
        $offer = $this->service->create($request->user(), $request->validated());

        return response()->json(['offer' => $offer], 201);
    }

    public function show(Offer $offer): JsonResponse
    {
        Gate::authorize('view', $offer);

        return response()->json(['offer' => $offer->load('embassy:id,official_name')]);
    }

    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        Gate::authorize('update', $offer);

        return response()->json(['offer' => $this->service->update($offer, $request->validated())]);
    }

    public function destroy(Offer $offer): JsonResponse
    {
        Gate::authorize('delete', $offer);
        $offer->delete();

        return response()->json(['message' => 'Offre supprimée.']);
    }

    public function submit(Request $request, Offer $offer): JsonResponse
    {
        Gate::authorize('submit', $offer);

        return response()->json(['offer' => $this->service->submit($offer, $request->user())]);
    }

    public function publish(Request $request, Offer $offer): JsonResponse
    {
        Gate::authorize('publish', $offer);

        return response()->json(['offer' => $this->service->publish($offer)]);
    }

    public function pause(Offer $offer): JsonResponse
    {
        Gate::authorize('pause', $offer);

        return response()->json(['offer' => $this->service->pause($offer)]);
    }

    public function close(Offer $offer): JsonResponse
    {
        Gate::authorize('close', $offer);

        return response()->json(['offer' => $this->service->close($offer)]);
    }

    public function duplicate(Request $request, Offer $offer): JsonResponse
    {
        Gate::authorize('duplicate', $offer);

        return response()->json(['offer' => $this->service->duplicate($offer, $request->user())], 201);
    }
}