<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivationRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Models\UserInvitation;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function create() { return view('auth.activation-request'); }
    public function send(ActivationRequest $request, InvitationService $invitations): RedirectResponse
    {
        $invitations->sendForEmail($request->validated('email'));
        return back()->with('status', 'Jika email Anda terdaftar, tautan aktivasi telah dikirim. Periksa kotak masuk Anda.');
    }
    public function edit(Request $request, string $token)
    {
        $invitation = UserInvitation::query()->where('token_hash', hash('sha256', $token))->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())->whereIn('email', \App\Models\AllowedActivationEmail::query()->where('enabled', true)->whereIn('role', ['admin', 'editor'])->select('email'))->whereHas('user', fn ($query) => $query->where('status', 'invited'))->first();
        abort_unless($invitation, 404);
        return response()->view('auth.activation-password', ['token' => $token, 'email' => $invitation->email], 200, ['Referrer-Policy' => 'no-referrer', 'Cache-Control' => 'no-store']);
    }
    public function update(SetPasswordRequest $request, string $token, InvitationService $invitations): RedirectResponse
    {
        $data = $request->validated(); $user = $invitations->consume($token, $data['email'], $data['name'], $data['password']);
        if (! $user) return redirect()->route('activation.create')->withErrors(['email' => 'Tautan aktivasi tidak berlaku. Minta tautan baru bila diperlukan.']);
        $request->session()->forget(['auth.pending_user_id', 'auth.pending_auth_version']);
        $request->session()->regenerate();
        return redirect()->route('login')->with('status', 'Kata sandi berhasil dibuat. Silakan masuk menggunakan email dan kata sandi Anda. Kode OTP akan dikirim saat login.');
    }
}
