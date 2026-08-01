<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // The candidate

            $table->string('status')->default('submitted');
            // submitted|processing|shortlisted|interview|accepted|rejected|waitlisted|withdrawn

            $table->text('cover_letter')->nullable();
            $table->string('cover_letter_file')->nullable(); // MinIO key if uploaded as PDF

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();

            // Eligibility check results (stored at time of submission)
            $table->boolean('eligibility_passed')->default(false);
            $table->json('eligibility_details')->nullable();

            // Answers to offer-specific questions (json array of {question_id, answer})
            $table->json('answers')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // One active application per offer per candidate
            $table->unique(['offer_id', 'user_id']);

            $table->index(['offer_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('changed_by_user_id')->nullable(); // UUID, nullable for system transitions
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('application_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable(); // 1-100
            $table->text('comment')->nullable(); // Internal only, never visible to candidate
            $table->json('criteria_scores')->nullable(); // {criterion_id: score}
            $table->timestamps();

            $table->unique(['application_id', 'evaluator_id']);
        });

        // Offer-specific custom questions
        Schema::create('offer_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('offer_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->string('type')->default('text'); // text|choice|boolean|file
            $table->json('options')->nullable(); // For choice type
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        // Offer-specific evaluation criteria
        Schema::create('offer_evaluation_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('offer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('weight')->default(1); // Relative weight
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_evaluation_criteria');
        Schema::dropIfExists('offer_questions');
        Schema::dropIfExists('application_evaluations');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('applications');
    }
};
