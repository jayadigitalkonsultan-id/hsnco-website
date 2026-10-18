<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\EmailOtpService;
use App\Services\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function create() { return view('auth.login'); }
    public function store(LoginRequest $request, EmailOtpService $otp): RedirectResponse
    {
        $data = $request->validated(); $email = $data['email'];
        $key = 'login:identity:'.hash('sha256', $email.'|'.$request->ip()); $ipKey = 'login:ip:'.$request->ip();
        $generic = fn () => back()->withInput($request->safe()->except('password'))->withErrors(['email' => 'Email atau kata sandi tidak dapat digunakan.']);
        if (RateLimiter::tooManyAttempts($key, config('security.login.attempts_per_minute')) || RateLimiter::tooManyAttempts($ipKey, config('security.login.ip_attempts_per_minute'))) {
            app(SecurityAudit::class)->record('login_rate_limited', 'blocked', null, $request, 'rate_limit', ['identity' => hash('sha256', $email)]); return $generic();
        }
        RateLimiter::hit($key, 60); RateLimiter::hit($ipKey, 60);
        $user = User::query()->where('email', $email)->first();
        if (! $user) { Hash::check($data['password'], '$2y$12$3qSoSws22J5Hga.8Rf/82.Oolxk3u0eQAlpFM4t6n0sVh7VgvKq7O'); app(SecurityAudit::class)->record('login_failed','failed',null,$request,'invalid_credentials',['identity'=>hash('sha256',$email)]); return $generic(); }
        if ($user->locked_until?->isFuture() || $user->status === UserStatus::Inactive || ! $user->password) { app(SecurityAudit::class)->record('login_failed','failed',$user,$request,'invalid_credentials'); return $generic(); }
        if (! Auth::attempt(['email' => $email, 'password' => $data['password']])) { $this->failed($user, $request); return $generic(); }
        $request->session()->regenerate();
        $request->session()->put('auth.pending_user_id', $user->id);
        $request->session()->put('auth.pending_auth_version', $user->auth_version);
        $user->forceFill(['failed_login_attempts'=>0,'last_failed_login_at'=>null,'locked_until'=>null])->save();
        if (! $otp->send($user, $request, $user->status === UserStatus::PendingTwoFactor ? 'enrollment' : 'login')) return $this->logoutForDeliveryFailure($request);
        app(SecurityAudit::class)->record('password_verified', 'success', $user, $request);
        return redirect()->route('two-factor.challenge');
    }
    private function failed(User $user, Request $request): void
    {
        $now = now(); $window = now()->subMinutes(config('security.login.failure_window_minutes'));
        $count = $user->last_failed_login_at?->lt($window) ? 1 : $user->failed_login_attempts + 1;
        $changes = ['failed_login_attempts'=>$count,'last_failed_login_at'=>$now];
        if ($count >= config('security.login.failures_before_lock')) { $changes['locked_until'] = $now->copy()->addMinutes(config('security.login.lock_minutes')); $changes['failed_login_attempts'] = 0; }
        $user->forceFill($changes)->save(); app(SecurityAudit::class)->record(isset($changes['locked_until']) ? 'login_locked' : 'login_failed', 'failed', $user, $request, 'invalid_credentials');
    }
    private function logoutForDeliveryFailure(Request $request): RedirectResponse { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login')->withErrors(['email'=>'Kami tidak dapat mengirim kode verifikasi saat ini. Silakan coba lagi.']); }
    public function destroy(Request $request): RedirectResponse { $user = $request->user(); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); if ($user) app(SecurityAudit::class)->record('logout','success',$user,$request); return redirect()->route('login'); }
}
