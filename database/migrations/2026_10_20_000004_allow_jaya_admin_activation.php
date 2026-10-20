<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $email = 'jaya.digitalkonsultan@gmail.com';
        DB::transaction(function () use ($email) {
            DB::table('activation_email_allowlist')->insertOrIgnore([
                'email' => $email, 'role' => 'admin', 'enabled' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('activation_email_allowlist')->where('email', $email)->update([
                'role' => 'admin', 'enabled' => true, 'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Permission revocation requires an explicit decision. Rolling back code
        // must not remove an entry that may have been managed manually before.
    }
};