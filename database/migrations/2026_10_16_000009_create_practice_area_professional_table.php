<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_area_professional', function (Blueprint $table) {
            $table->foreignUlid('practice_area_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('professional_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['practice_area_id', 'professional_id'], 'practice_professional_pk');
            $table->index('professional_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_area_professional');
    }
};
