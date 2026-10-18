<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('submission_key')->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 254);
            $table->string('phone', 40)->nullable();
            $table->foreignUlid('practice_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('area_label');
            $table->text('message');
            $table->string('source', 20);
            $table->string('status', 20)->default('new');
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
