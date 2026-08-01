<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Personal info
            $table->string('wilaya', 60)->nullable();
            $table->string('commune', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable(); // male|female|other
            $table->string('national_id', 50)->nullable();
            $table->string('bio')->nullable();

            // Current academic situation
            $table->string('current_institution', 200)->nullable();
            $table->string('current_level', 50)->nullable(); // bac|licence|master|doctorat|postdoc|professional
            $table->string('current_field', 200)->nullable();
            $table->string('current_year', 10)->nullable();

            // External links
            $table->string('linkedin_url')->nullable();
            $table->string('researchgate_url')->nullable();
            $table->string('orcid')->nullable();
            $table->string('google_scholar_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('personal_website')->nullable();

            // Generic cover letter (editable per application)
            $table->text('cover_letter_template')->nullable();

            // Privacy settings (which fields are visible to embassy admins)
            $table->json('visibility_settings')->nullable();

            // Profile completeness (computed, cached)
            $table->unsignedTinyInteger('completeness_pct')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
        });

        Schema::create('candidate_educations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree'); // licence|master|doctorat|ingénieur|bts|autre
            $table->string('field');
            $table->string('grade')->nullable(); // mention / GPA
            $table->year('start_year');
            $table->year('end_year')->nullable();
            $table->boolean('current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('language', 10); // fr|ar|en|de|es|zh|ru...
            $table->string('level', 5); // A1|A2|B1|B2|C1|C2|native
            $table->timestamps();
        });

        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // tech|soft|tool|other
            $table->timestamps();
        });

        Schema::create('candidate_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('journal')->nullable();
            $table->year('year');
            $table->string('doi')->nullable();
            $table->string('url')->nullable();
            $table->string('type', 30)->default('article'); // article|conference|book|thesis|patent
            $table->timestamps();
        });

        Schema::create('candidate_awards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('issuer')->nullable();
            $table->year('year');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_awards');
        Schema::dropIfExists('candidate_publications');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_languages');
        Schema::dropIfExists('candidate_experiences');
        Schema::dropIfExists('candidate_educations');
        Schema::dropIfExists('candidate_profiles');
    }
};
