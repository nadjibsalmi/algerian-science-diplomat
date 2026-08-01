<?php

namespace App\Modules\Applications\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Requests\StoreApplicationRequest;
use App\Modules\Applications\Requests\UpdateApplicationStatusRequest;
use App\Modules\Applications\Requests\AttachApplicationDocumentsRequest;
use App\Modules\Applications\Services\ApplicationService;
use App\Modules\Offers\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $service) {}

    /** Candidate: list my applications */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Application::class);

        $applications = Application::with(['offer.embassy'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Candidate/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    /** Candidate: view one of my applications + conversation */
    public function show(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $application->load([
            'offer.embassy',
            'statusHistories',
            'documents',
            'conversation.messages.sender',
        ]);

        return Inertia::render('Candidate/Applications/Show', [
            'application' => $application,
        ]);
    }

    /** Candidate: submit a new application */
    public function store(StoreApplicationRequest $request, Offer $offer): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $application = $this->service->submit($request->user(), $offer, $request->validated());

        return redirect()->route('candidate.applications.show', $application)
            ->with('status', __('applications.submitted_successfully'));
    }

    /** Candidate: withdraw their application */
    public function withdraw(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('withdraw', $application);
        $this->service->withdraw($application, $request->user());

        return back()->with('status', __('applications.withdrawn'));
    }

    /** Embassy admin: list applications for an offer */
    public function offerApplications(Request $request, Offer $offer): Response
    {
        $this->authorize('update', $offer);

        $applications = Application::with(['candidate.candidateProfile', 'evaluations'])
            ->where('offer_id', $offer->id)
            ->orderByDesc('submitted_at')
            ->paginate(25);

        return Inertia::render('Embassy/Applications/Index', [
            'offer'        => $offer->load('embassy'),
            'applications' => $applications,
        ]);
    }

    /** Embassy admin: update application status */
    public function updateStatus(UpdateApplicationStatusRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('updateStatus', $application);
        $this->service->changeStatus($application, $request->status, $request->user(), $request->note);

        return back()->with('status', __('applications.status_updated'));
    }

    /** Candidate: attach additional clean documents to an existing application */
    public function attachDocuments(
        AttachApplicationDocumentsRequest $request,
        Application $application,
    ): JsonResponse {
        Gate::authorize('attachDocuments', $application);

        return response()->json([
            'application' => $this->service->attachDocuments(
                $application,
                $request->user(),
                $request->validated('document_ids'),
            ),
        ]);
    }
}
