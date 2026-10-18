<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $idColumn = collect(Schema::getColumns('users'))->firstWhere('name', 'id');
        if (! in_array($idColumn['type_name'] ?? null, ['char', 'varchar', 'text'], true)) {
            throw new RuntimeException('Run database:preflight and prepare a ULID mapping before changing an existing integer-key user table.');
        }
        if (DB::table('users')->exists()) {
            throw new RuntimeException('Existing accounts require an explicit role/status/onboarding backfill before this migration. No account data has been changed.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('status', 20)->default('invited')->index();
            $table->string('role', 20)->default('editor');
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_login_at')->nullable();
            $table->timestamp('locked_until')->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->foreignUlid('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->unsignedInteger('auth_version')->default(0);
        });
    }

    public function down(): void
    {
        // Refuse a lossy rollback when invited accounts have no name/password yet.
        if (DB::table('users')->whereNull('name')->orWhereNull('password')->exists()) {
            throw new RuntimeException('Complete or explicitly remove pending invitations before rolling back the user schema.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropIndex(['status']);
            $table->dropIndex(['locked_until']);
            $table->dropColumn(['status', 'role', 'failed_login_attempts', 'last_failed_login_at', 'locked_until', 'last_login_at', 'last_login_ip', 'password_changed_at', 'activated_at', 'auth_version']);
            $table->string('name')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
