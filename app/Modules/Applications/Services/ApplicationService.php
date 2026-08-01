<?php

namespace App\Modules\Applications\Services;

use App\Models\User;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Notifications\ApplicationStatusChanged;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Offers\Models\Offer;
use App\Modules\Documents\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function submit(User $candidate, Offer $offer, array $data): Application
    {
        abort_unless($offer->status === 'published', 422, 'Cette offre n’est plus ouverte.');
        // Check for duplicate
        if (Application::where('offer_id', $offer->id)->where('user_id', $candidate->id)->exists()) {
            abort(422, __('applications.already_applied'));
        }

        // Check eligibility
        $eligibilityService = app(EligibilityService::class);
        $eligibility = $eligibilityService->check($candidate, $offer);

        if (! $eligibility['passed']) {
            abort(422, __('applications.not_eligible'), ['details' => $eligibility]);
        }

        return DB::transaction(function () use ($candidate, $offer, $data, $eligibility) {
            $this->ensureCandidateDocuments($candidate, $data['document_ids'] ?? []);
            $application = Application::create([
                'offer_id'            => $offer->id,
                'user_id'             => $candidate->id,
                'status'              => 'submitted',
                'cover_letter'        => $data['cover_letter'] ?? null,
                'answers'             => $data['answers'] ?? null,
                'submitted_at'        => now(),
                'eligibility_passed'  => $eligibility['passed'],
                'eligibility_details' => $eligibility['details'] ?? null,
            ]);

            // Attach documents
            if (! empty($data['document_ids'])) {
                $docData = collect($data['document_ids'])->mapWithKeys(fn ($id) => [$id => ['role' => 'submitted']]);
                $application->documents()->sync($docData);
            }

            // Create conversation thread for this application
            Conversation::create(['application_id' => $application->id]);

            // Record initial status history
            $application->statusHistories()->create([
                'from_status' => null,
                'to_status'   => 'submitted',
            ]);

            activity()->causedBy($candidate)->performedOn($application)->log('Application submitted');

            return $application;
        });
    }

    public function changeStatus(Application $application, string $newStatus, User $changedBy, ?string $note = null): void
    {
        $this->ensureValidTransition($application->status, $newStatus);
        $application->transitionStatus($newStatus, $changedBy, $note);

        // Notify the candidate asynchronously
        $application->candidate->notify(new ApplicationStatusChanged($application));
    }

    public function withdraw(Application $application, User $candidate): void
    {
        abort_unless($application->user_id === $candidate->id, 403);
        abort_if(in_array($application->status, ['accepted', 'rejected'], true), 422, __('applications.cannot_withdraw'));

        $application->transitionStatus('withdrawn', $candidate, __('applications.candidate_withdrawn'));
        $application->update(['withdrawn_at' => now()]);
    }

    public function attachDocuments(Application $application, User $candidate, array $documentIds): Application
    {
        abort_unless($application->user_id === $candidate->id, 403);
        $this->ensureCandidateDocuments($candidate, $documentIds);
        $application->documents()->syncWithoutDetaching(
            collect($documentIds)->mapWithKeys(fn (string $id) => [$id => ['role' => 'submitted']])->all(),
        );

        return $application->refresh()->load('documents');
    }

    private function ensureCandidateDocuments(User $candidate, array $documentIds): void
    {
        if (empty($documentIds)) {
            return;
        }

        $count = Document::query()
            ->whereIn('id', $documentIds)
            ->where('user_id', $candidate->id)
            ->where('status', Document::STATUS_CLEAN)
            ->count();

        if ($count !== count(array_unique($documentIds))) {
            throw ValidationException::withMessages([
                'document_ids' => 'Tous les documents doivent appartenir au candidat et être validés.',
            ]);
        }
    }

    private function ensureValidTransition(string $from, string $to): void
    {
        $allowed = [
            'submitted' => ['processing', 'rejected', 'withdrawn'],
            'processing' => ['shortlisted', 'interview', 'rejected', 'waitlisted'],
            'shortlisted' => ['interview', 'accepted', 'rejected', 'waitlisted'],
            'interview' => ['accepted', 'rejected', 'waitlisted'],
            'waitlisted' => ['accepted', 'rejected'],
        ];

        if (! in_array($to, $allowed[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Transition impossible depuis l’état {$from}.",
            ]);
        }
    }
}
