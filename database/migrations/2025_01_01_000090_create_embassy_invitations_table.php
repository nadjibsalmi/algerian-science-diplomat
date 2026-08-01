<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embassy_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('embassy_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role_in_embassy'); // director|recruiter|hr
            $table->string('token', 64)->unique();
            $table->foreignUuid('invited_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'expires_at']);
            $table->index(['embassy_id', 'email']);
        });

        // Add 2FA columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('2fa_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->boolean('login_notification_enabled')->default(true)->after('two_factor_recovery_codes');
            $table->json('consent_log')->nullable()->after('login_notification_enabled');
        });

        // Offer view tracking for analytics
        Schema::create('offer_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable(); // Hashed IP for anonymous tracking
            $table->timestamps();

            $table->index(['offer_id', 'created_at']);
        });

        // Platform settings key-value store
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // User suspension audit trail
        Schema::create('user_suspensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('suspended_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->timestamp('suspended_until')->nullable(); // null = indefinite
            $table->timestamp('lifted_at')->nullable();
            $table->foreignUuid('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_suspensions');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('offer_views');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'login_notification_enabled', 'consent_log']);
        });
        Schema::dropIfExists('embassy_invitations');
    }
};
