<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embassies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country');
            $table->string('official_name');
            $table->string('logo')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('status')->default('active'); // active, suspended
            $table->timestamps();
            $table->softDeletes();

            $table->index('country');
            $table->index('status');
        });

        // Pivot: which users belong to which embassy, and in what capacity
        // (director, recruiter, hr) - this is the actual enforcement point
        // for tenant isolation: a user's accessible embassy_id set is
        // derived from this table, never from a client-supplied parameter.
        Schema::create('embassy_user', function (Blueprint $table) {
            // AUDIT FIX: previously had its own uuid('id') primary key,
            // but nothing populates a synthetic UUID automatically on a
            // plain belongsToMany()->attach() call - confirmed live via
            // CI: "NOT NULL constraint failed: embassy_user.id". A
            // composite primary key of the two real foreign keys is the
            // standard, idiomatic Laravel pivot-table pattern and needs
            // no extra wiring at all.
            $table->foreignUuid('embassy_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_in_embassy'); // director, recruiter, hr
            $table->timestamps();

            $table->primary(['embassy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embassy_user');
        Schema::dropIfExists('embassies');
    }
};
