<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('revisionable_type', 50);
            $table->ulid('revisionable_id');
            $table->unsignedInteger('version');
            $table->string('status', 20)->default('draft');
            $table->json('payload');
            $table->text('review_note')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['revisionable_type', 'revisionable_id', 'version'], 'revisions_target_version_unique');
            $table->index(['revisionable_type', 'revisionable_id', 'status'], 'revisions_target_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};
