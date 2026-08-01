<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AUDIT FIX: same class of bug as the permission-tables migration - the
 * User/Embassy/Offer models all use spatie/laravel-activitylog's
 * LogsActivity trait, but the package's own required migration was
 * never added. Content matches the package's own official migration stub,
 * adapted for UUID subject/causer IDs (this project's users and business
 * models both use UUID primary keys, not auto-incrementing integers).
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = config('activitylog.database_connection');

        Schema::connection($connection)->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();
            $table->uuid('causer_id')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))
            ->dropIfExists(config('activitylog.table_name'));
    }
};
