<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->foreignUuid('submitted_by')->nullable()->after('embassy_id')->constrained('users')->nullOnDelete();
            $table->foreignUuid('moderated_by')->nullable()->after('submitted_by')->constrained('users')->nullOnDelete();
            $table->string('moderation_status')->default('not_reviewed')->after('status');
            $table->text('moderation_notes')->nullable()->after('moderation_status');
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->uuid('duplicated_from_id')->nullable()->after('closed_at');

            $table->index(['moderation_status', 'status']);
            $table->index('submitted_at');
            $table->foreign('duplicated_from_id')->references('id')->on('offers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['moderated_by']);
            $table->dropForeign(['duplicated_from_id']);
            $table->dropIndex(['moderation_status', 'status']);
            $table->dropIndex(['submitted_at']);
            $table->dropColumn([
                'submitted_by',
                'moderated_by',
                'moderation_status',
                'moderation_notes',
                'submitted_at',
                'duplicated_from_id',
            ]);
        });
    }
};