<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Mail\EmailOtpMail;
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

    public function test_invitation_activation_requires_matching_single_use_token_then_returns_to_login(): void
    {
        $admin = User::factory()->active('super_admin')->create();
        $user = User::factory()->create(['email'=>'invitee@example.test','password'=>null,'status'=>UserStatus::Invited]);
        \App\Models\AllowedActivationEmail::create(['email' => $user->email, 'role' => 'editor']);
        $plain = str_repeat('A', 64);
        $invitation = UserInvitation::factory()->create(['user_id'=>$user->id,'email'=>$user->email,'token_hash'=>hash('sha256',$plain),'expires_at'=>now()->addHour()]);
        $this->post(route('activation.update',$plain), ['email'=>$user->email,'name'=>'Invitee','password'=>'Password!1234','password_confirmation'=>'Password!1234'])->assertRedirect(route('login'));
        $user->refresh(); $invitation->refresh();
        $this->assertSame(UserStatus::PendingTwoFactor, $user->status); $this->assertTrue(Hash::check('Password!1234',$user->password)); $this->assertNotNull($invitation->accepted_at); $this->assertNull($invitation->token_hash);
        $this->assertGuest();
        $this->post(route('activation.update',$plain), ['email'=>$user->email,'name'=>'Again','password'=>'Password!1234','password_confirmation'=>'Password!1234'])->assertRedirect(route('activation.create'));
    }

    public function test_password_login_needs_email_otp_before_admin_access(): void
    {
        Mail::fake(); config(['admin.local_preview'=>false]);
        $user = User::factory()->active()->unverified()->create(['password'=>Hash::make('Password!1234')]);
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

    public function test_dummy_hash_check_cannot_authenticate_an_unknown_email_even_when_it_matches(): void
    {
        Hash::partialMock()->shouldReceive('check')->once()->andReturn(true);
        $this->post(route('login.store'), ['email' => 'unknown@example.test', 'password' => 'dummy-password'])
            ->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event' => 'login_failed', 'reason' => 'invalid_credentials']);
        $this->assertDatabaseCount('two_factor_challenges', 0);
    }

    public function test_verified_email_and_confirmed_factor_allow_login_without_sending_another_otp(): void
    {
        Mail::fake();
        config(['admin.local_preview' => false]);
        $user = User::factory()->active('admin')->create(['password' => Hash::make('Password!1234')]);
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password!1234'])
            ->assertRedirect(route('admin.dashboard'))->assertSessionHas('auth.two_factor.user_id', $user->id)
            ->assertSessionMissing('auth.pending_user_id');
        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertOk();
        Mail::assertNothingSent();
        $this->assertDatabaseCount('two_factor_challenges', 0);
    }

    public function test_successful_first_otp_marks_email_verified_and_subsequent_login_skips_otp(): void
    {
        Mail::fake();
        $user = User::factory()->active('admin')->unverified()->create(['password' => Hash::make('Password!1234')]);
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now()]);
        $login = $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password!1234'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->withCookie(config('session.cookie'), $login->getCookie(config('session.cookie'))->getValue());
        $this->post(route('two-factor.verify'), ['code' => Mail::sent(EmailOtpMail::class)->first()->code])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->post(route('logout'))->assertRedirect(route('login'));
        Mail::fake();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password!1234'])
            ->assertRedirect(route('admin.dashboard'));
        Mail::assertNothingSent();
    }

    public function test_inactive_and_temporarily_locked_accounts_cannot_login_with_a_correct_password(): void
    {
        foreach ([['status' => UserStatus::Inactive], ['locked_until' => now()->addMinutes(15)]] as $state) {
            $user = User::factory()->active()->create($state + ['password' => Hash::make('Password!1234')]);
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password!1234'])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_login_is_rate_limited_before_more_password_checks(): void
    {
        config(['security.login.attempts_per_minute' => 2]);
        $user = User::factory()->active()->create();
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        }
        $this->assertGuest();
        $this->assertSame(2, $user->fresh()->failed_login_attempts);
        $this->assertDatabaseHas('audit_logs', ['event' => 'login_rate_limited']);
    }
}
