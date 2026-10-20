<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_return_from_home_to_dashboard(): void
    {
        config(['admin.local_preview'=>false]);
        $user=User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','confirmed_at'=>now()]);
        $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]]);
        $this->get(route('home'))->assertOk()->assertSee(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_expired_verification_returns_to_login_without_home_redirect_loop(): void
    {
        $user=User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','confirmed_at'=>now()]);
        $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()-86400]]);
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get(route('login'))->assertOk();
    }

    public function test_pending_login_still_requires_otp(): void
    {
        $user=User::factory()->active('admin')->create();
        $this->actingAs($user)->withSession(['auth.pending_user_id'=>$user->id,'auth.pending_auth_version'=>$user->auth_version]);
        $this->get(route('admin.dashboard'))->assertRedirect(route('two-factor.challenge'));
        $this->get(route('two-factor.challenge'))->assertOk();
    }

    public function test_orphaned_otp_session_can_return_to_login(): void
    {
        $this->actingAs(User::factory()->active()->create());
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('login'))->assertOk();
    }
}