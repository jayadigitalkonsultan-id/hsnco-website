<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivationRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Models\UserInvitation;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $valid = UserInvitation::query()->where('token_hash', hash('sha256', $token))->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())->exists();
        abort_unless($valid, 404);
        return response()->view('auth.activation-password', ['token' => $token], 200, ['Referrer-Policy' => 'no-referrer', 'Cache-Control' => 'no-store']);
    }
    public function update(SetPasswordRequest $request, string $token, InvitationService $invitations): RedirectResponse
    {
        $data = $request->validated(); $user = $invitations->consume($token, $data['email'], $data['name'], $data['password']);
        if (! $user) return redirect()->route('activation.create')->withErrors(['email' => 'Tautan aktivasi tidak berlaku. Minta tautan baru bila diperlukan.']);
        Auth::login($user); $request->session()->regenerate();
        $request->session()->put('auth.pending_user_id', $user->id); $request->session()->put('auth.pending_auth_version', $user->auth_version);
        return redirect()->route('two-factor.challenge');
    }
}
