<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_usages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('media_id')->constrained('media')->restrictOnDelete();
            $table->string('usable_type', 50);
            $table->ulid('usable_id');
            $table->string('field', 100);
            $table->timestamps();
            $table->unique(['media_id', 'usable_type', 'usable_id', 'field'], 'media_usage_unique');
            $table->index(['usable_type', 'usable_id'], 'media_usage_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
