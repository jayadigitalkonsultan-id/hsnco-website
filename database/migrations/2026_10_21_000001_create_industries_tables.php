<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug', 160)->unique();
            $table->string('english_name')->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->json('focus')->nullable();
            $table->foreignUlid('image_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('illustration_path')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('published_revision')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order']);
        });
        Schema::create('industry_professional', function (Blueprint $table) {
            $table->foreignUlid('industry_id')->constrained('industries')->cascadeOnDelete();
            $table->foreignUlid('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['industry_id', 'professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_professional');
        Schema::dropIfExists('industries');
    }
};
