<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Models\User;
use App\Services\EmailOtpService;
use App\Services\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    private function pending(Request $request): ?User { $user = $request->user(); return $user && $request->session()->get('auth.pending_user_id') === $user->id && $request->session()->get('auth.pending_auth_version') === $user->auth_version ? $user : null; }
    public function create(Request $request) { $user = $this->pending($request); if (! $user) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); } return view('auth.otp', ['email' => $this->mask($user->email)]); }
    public function verify(OtpRequest $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $this->pending($request); if (! $user) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
        $result = $otp->verify($user, $request, $request->validated('code'), $user->status->value === 'pending_2fa' ? 'enrollment' : 'login');
        if (! $result['ok']) return back()->withErrors(['code' => 'Kode tidak valid atau sudah kedaluwarsa.']);
        $request->session()->regenerate(); $request->session()->forget(['auth.pending_user_id', 'auth.pending_auth_version']);
        $request->session()->put('auth.two_factor', ['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]);
        $user->forceFill(['last_login_at'=>now(),'last_login_ip'=>$request->ip()])->save(); app(SecurityAudit::class)->record('login_success','success',$user,$request);
        if ($result['recovery_codes']) { $request->session()->put('auth.recovery_codes', $result['recovery_codes']); return redirect()->route('two-factor.recovery-codes'); }
        return redirect()->intended(route('admin.dashboard'));
    }
    public function resend(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $this->pending($request); if (! $user) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
        $sent = $otp->send($user, $request, $user->status->value === 'pending_2fa' ? 'enrollment' : 'login');
        return back()->with($sent ? 'status' : 'error', $sent ? 'Kode baru dikirim.' : 'Kode belum dapat dikirim. Tunggu sebentar lalu coba lagi.');
    }
    public function recoveryCodes(Request $request) { $codes = $request->session()->pull('auth.recovery_codes', []); if (! $codes) return redirect()->route('admin.dashboard'); return view('auth.recovery-codes', compact('codes')); }
    private function mask(string $email): string { [$name,$domain] = explode('@',$email,2); return mb_substr($name,0,1).str_repeat('•', max(2,mb_strlen($name)-1)).'@'.$domain; }
}
