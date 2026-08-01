<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->string('type'); // diploma|transcript|cv|recommendation|passport|birth_cert|address_proof|publication|cover_letter|work_cert|id_photo|other
            $table->string('name'); // User-defined label
            $table->string('original_filename');
            $table->string('path'); // MinIO object key
            $table->string('disk')->default('s3');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('status')->default('pending'); // pending|scanning|clean|infected|rejected
            $table->string('virus_scan_result')->nullable();
            $table->timestamp('virus_scanned_at')->nullable();

            // Versioning: documents can be replaced while keeping history
            $table->uuid('parent_document_id')->nullable()->index(); // Previous version
            $table->unsignedSmallInteger('version')->default(1);

            // Expiry (for passports, time-limited certs, etc.)
            $table->date('expires_at')->nullable();

            // Temporary share token (24h presigned URL alternative for in-app sharing)
            $table->string('share_token', 64)->nullable()->unique();
            $table->timestamp('share_token_expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
        });

        // Track which documents are attached to which applications
        Schema::create('application_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('document_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable(); // required|optional
            $table->timestamps();

            $table->unique(['application_id', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('documents');
    }
};
