<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_notes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['enquiry_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_notes');
    }
};
