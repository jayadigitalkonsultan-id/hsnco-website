<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug', 160)->unique();
            $table->string('position', 150);
            $table->string('level', 30);
            $table->text('summary')->nullable();
            $table->longText('biography')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('languages')->nullable();
            $table->foreignUlid('photo_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->string('email', 254)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_leadership')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('published_revision')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'level', 'sort_order'], 'professionals_status_level_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
