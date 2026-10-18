<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('page_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('type', 50);
            $table->string('heading')->nullable();
            $table->json('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['page_id', 'key']);
            $table->index(['page_id', 'is_visible', 'sort_order'], 'sections_page_visible_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
