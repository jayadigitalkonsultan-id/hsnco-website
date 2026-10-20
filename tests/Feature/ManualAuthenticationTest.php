<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ManualAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_admin_redirects_to_login_outside_local_preview(): void
    {
        config(['admin.local_preview' => false]);
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_invitation_activation_requires_matching_single_use_token_then_enters_otp_enrollment(): void
    {
        $admin = User::factory()->active('super_admin')->create();
        $user = User::factory()->create(['email'=>'invitee@example.test','password'=>null,'status'=>UserStatus::Invited]);
        \App\Models\AllowedActivationEmail::create(['email' => $user->email, 'role' => 'editor']);
        $plain = str_repeat('A', 64);
        $invitation = UserInvitation::factory()->create(['user_id'=>$user->id,'email'=>$user->email,'token_hash'=>hash('sha256',$plain),'expires_at'=>now()->addHour()]);
        $this->post(route('activation.update',$plain), ['email'=>$user->email,'name'=>'Invitee','password'=>'Password!1234','password_confirmation'=>'Password!1234'])->assertRedirect(route('two-factor.challenge'));
        $user->refresh(); $invitation->refresh();
        $this->assertSame(UserStatus::PendingTwoFactor, $user->status); $this->assertTrue(Hash::check('Password!1234',$user->password)); $this->assertNotNull($invitation->accepted_at); $this->assertNull($invitation->token_hash);
        $this->post(route('logout'));
        $this->post(route('activation.update',$plain), ['email'=>$user->email,'name'=>'Again','password'=>'Password!1234','password_confirmation'=>'Password!1234'])->assertRedirect(route('activation.create'));
    }

    public function test_password_login_needs_email_otp_before_admin_access(): void
    {
        Mail::fake(); config(['admin.local_preview'=>false]);
        $user = User::factory()->active()->create(['password'=>Hash::make('Password!1234')]);
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','confirmed_at'=>now()]);
        $this->post(route('login.store'), ['email'=>$user->email,'password'=>'Password!1234'])->assertRedirect(route('two-factor.challenge'));
        $this->get('/admin')->assertRedirect(route('two-factor.challenge'));
        $this->assertDatabaseHas('two_factor_challenges',['user_id'=>$user->id,'purpose'=>'login']);
    }

    public function test_failed_password_attempts_lock_account_without_authenticating_it(): void
    {
        $user = User::factory()->active()->create(['password'=>Hash::make('Password!1234')]);
        for($i=0;$i<5;$i++) $this->post(route('login.store'), ['email'=>$user->email,'password'=>'incorrect'])->assertSessionHasErrors('email');
        $this->assertNotNull($user->fresh()->locked_until); $this->assertGuest();
        $this->assertDatabaseHas('audit_logs',['user_id'=>$user->id,'event'=>'login_locked']);
    }
}
