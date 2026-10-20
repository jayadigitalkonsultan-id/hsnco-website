<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('gmail_mail_connections',function(Blueprint $table){
   $table->id();$table->string('email',254)->unique();$table->text('access_token');$table->text('refresh_token');$table->timestamp('expires_at');$table->timestamps();
  });
  Schema::create('gmail_oauth_states',function(Blueprint $table){
   $table->char('state_hash',64)->primary();$table->string('expected_email',254);$table->text('code_verifier');$table->text('redirect_uri');$table->timestamp('expires_at')->index();$table->timestamp('used_at')->nullable();$table->timestamps();
  });
 }
 public function down(): void {Schema::dropIfExists('gmail_oauth_states');Schema::dropIfExists('gmail_mail_connections');}
};