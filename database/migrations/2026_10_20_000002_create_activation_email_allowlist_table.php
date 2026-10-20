<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activation_email_allowlist', function (Blueprint $table) {
            $table->string('email', 254)->primary();
            $table->string('role', 20);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        foreach ([
            'arvinz24wicaksono@gmail.com' => 'admin',
            'hsandcoattorney@gmail.com' => 'admin',
            'hermaliaziah22@gmail.com' => 'editor',
        ] as $email => $role) {
            DB::table('activation_email_allowlist')->insert(['email' => $email, 'role' => $role, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('activation_email_allowlist'); }
};