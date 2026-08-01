<?php

namespace App\Modules\Documents\Services;

use App\Models\User;
use App\Modules\Documents\Jobs\ScanDocumentWithClamAv;
use App\Modules\Documents\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    private const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    public function upload(User $user, UploadedFile $file, array $data): Document
    {
        $this->enforceStorageQuota($user, $file->getSize());
        $this->validateMimeType($file);

        $path = $this->storeFile($user, $file);

        $version = 1;
        $parentId = null;

        // If replacing an existing document (versioning)
        if (isset($data['replaces_id'])) {
            $parent = Document::where('id', $data['replaces_id'])->where('user_id', $user->id)->firstOrFail();
            $version  = $parent->version + 1;
            $parentId = $parent->id;
        }

        $document = Document::create([
            'user_id'           => $user->id,
            'type'              => $data['type'],
            'name'              => $data['name'],
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'disk'              => config('filesystems.default', 's3'),
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'status'            => Document::STATUS_PENDING,
            'expires_at'        => $data['expires_at'] ?? null,
            'parent_document_id'=> $parentId,
            'version'           => $version,
        ]);

        // Dispatch async ClamAV scan
        ScanDocumentWithClamAv::dispatch($document)->onQueue('documents');

        activity()->causedBy($user)->performedOn($document)->log('Document uploaded');

        return $document;
    }

    public function delete(User $user, Document $document): void
    {
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();
        activity()->causedBy($user)->performedOn($document)->log('Document deleted');
    }

    public function share(User $user, Document $document): string
    {
        $this->ensureClean($document);
        abort_unless($document->user_id === $user->id || $user->hasRole('Super Admin'), 403);

        return $document->generateShareToken();
    }

    public function download(Document $document)
    {
        $this->ensureClean($document);

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type],
        );
    }

    public function temporaryUrl(Document $document, int $minutes = 30): string
    {
        $this->ensureClean($document);

        return $document->temporaryUrl(min(60, max(1, $minutes)));
    }

    public function resolveShareToken(string $token): Document
    {
        $document = Document::query()
            ->where('share_token', hash('sha256', $token))
            ->where('share_token_expires_at', '>', now())
            ->firstOrFail();
        $this->ensureClean($document);

        return $document;
    }

    private function ensureClean(Document $document): void
    {
        if ($document->status !== Document::STATUS_CLEAN) {
            throw ValidationException::withMessages([
                'document' => 'Le document doit être validé par l’analyse antivirus.',
            ]);
        }
    }

    private function storeFile(User $user, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;
        $path      = "candidates/{$user->id}/documents/{$filename}";

        Storage::disk(config('filesystems.default', 's3'))->putFileAs(
            "candidates/{$user->id}/documents",
            $file,
            $filename,
            ['visibility' => 'private'],
        );

        return $path;
    }

    private function enforceStorageQuota(User $user, int $fileSize): void
    {
        $quotaBytes = (int) config('asd.upload.candidate_quota_mb', 500) * 1024 * 1024;
        $usedBytes  = Document::where('user_id', $user->id)->whereNull('deleted_at')->sum('size_bytes');

        if ($usedBytes + $fileSize > $quotaBytes) {
            abort(422, __('documents.quota_exceeded'));
        }
    }

    private function validateMimeType(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            abort(422, __('documents.invalid_mime'));
        }

        $maxBytes = (int) config('asd.upload.max_size_mb', 20) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            abort(422, __('documents.file_too_large'));
        }
    }
}
