<?php

namespace App\Http\Middleware;

use App\Enums\TwoFactorMethod;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $verifiedAt = $request->session()->get('auth.two_factor.verified_at');
        $factor = $user?->twoFactorAuthentication;

        if (! $user || ! $user->isActive() || $factor?->method !== TwoFactorMethod::Email || ! $factor->confirmed_at
            || $request->session()->get('auth.two_factor.user_id') !== $user->id
            || $request->session()->get('auth.two_factor.auth_version') !== $user->auth_version
            || ! is_int($verifiedAt) || $verifiedAt <= time() - config('session.lifetime') * 60) {
            $request->session()->forget('auth.two_factor');
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
