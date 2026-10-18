<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\SecurityAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdministrator extends Command
{
    protected $signature = 'user:create-super-admin {--email=} {--name=}';
    protected $description = 'Create the first HSNCO Super Administrator without storing a default password';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) ($this->option('email') ?: $this->ask('Email'))));
        $name = trim((string) ($this->option('name') ?: $this->ask('Name')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') { $this->error('A valid email and name are required.'); return self::FAILURE; }
        if (User::query()->where('email', $email)->exists()) { $this->error('That email already exists.'); return self::FAILURE; }
        $password = $this->secret('Password (minimum 12 characters)');
        if (! is_string($password) || mb_strlen($password) < 12) { $this->error('Password must contain at least 12 characters.'); return self::FAILURE; }
        $user = User::query()->create(['name'=>$name,'email'=>$email,'password'=>Hash::make($password)]);
        $user->forceFill(['role'=>UserRole::SuperAdmin,'status'=>UserStatus::PendingTwoFactor,'email_verified_at'=>now(),'password_changed_at'=>now()])->save();
        app(SecurityAudit::class)->record('super_admin_created','success',$user,null);
        $this->info('Super Administrator created. Sign in and complete email OTP enrollment before accessing the dashboard.');
        return self::SUCCESS;
    }
}
