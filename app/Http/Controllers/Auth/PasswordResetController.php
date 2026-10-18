<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function create() { return view('auth.forgot-password'); }
    public function send(Request $request): RedirectResponse
    {
        $email = mb_strtolower(trim((string) $request->validate(['email'=>['required','email']])['email']));
        $user = User::query()->where('email',$email)->first();
        if ($user?->isActive()) Password::sendResetLink(['email'=>$email]);
        return back()->with('status','Jika akun Anda memenuhi syarat, tautan reset telah dikirim.');
    }
    public function edit(Request $request, string $token) { return view('auth.reset-password',['token'=>$token,'email'=>$request->query('email')]); }
    public function update(Request $request): RedirectResponse
    {
        $data=$request->validate(['token'=>['required'],'email'=>['required','email'],'password'=>['required','confirmed',PasswordRule::min(12)->mixedCase()->numbers()->symbols()]]);
        $status=Password::reset($data, function(User $user,string $password) use($request) { $user->forceFill(['password'=>Hash::make($password),'password_changed_at'=>now(),'auth_version'=>$user->auth_version+1,'remember_token'=>Str::random(60)])->save(); app(SecurityAudit::class)->record('password_reset','success',$user,$request); });
        return $status===Password::PASSWORD_RESET ? redirect()->route('login')->with('status','Kata sandi diperbarui. Masuk kembali dan verifikasi OTP email.') : back()->withErrors(['email'=>__($status)]);
    }
}
