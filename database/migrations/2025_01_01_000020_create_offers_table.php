<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // AUDIT-CRITICAL: this is THE column tenant isolation hinges
            // on. Every query for offers must filter by an embassy_id the
            // requesting user actually belongs to (see OfferPolicy) -
            // never trust a client-supplied embassy_id directly.
            $table->foreignUuid('embassy_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('offer_type'); // internship, scholarship, research, phd, postdoc, employment, exchange, volunteer, ngo, conference, training, competition
            $table->string('category')->nullable();
            $table->string('research_field')->nullable();
            $table->string('level')->nullable();
            $table->string('contract_type')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamp('deadline')->nullable();
            $table->string('status')->default('draft'); // draft, pending_approval, published, paused, closed, archived
            $table->string('visibility')->default('public'); // public, private
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['embassy_id', 'status']);
            $table->index('offer_type');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
