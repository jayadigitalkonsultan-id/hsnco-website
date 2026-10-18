<?php

namespace App\Http\Middleware;

use App\Enums\TwoFactorMethod;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            $preview = app()->environment('local') && config('admin.local_preview')
                && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
                && in_array($request->getHost(), ['localhost', '127.0.0.1', '::1', '[::1]'], true)
                && in_array($request->method(), ['GET', 'HEAD'], true);
            if (! $preview) {
                return redirect()->guest(route('login'));
            }
            $request->attributes->set('admin_preview', true);
        } else {
            $factor = $user->twoFactorAuthentication;
            $verifiedAt = $request->session()->get('auth.two_factor.verified_at');
            if (! ($user->isActive() && $factor?->method === TwoFactorMethod::Email && $factor->confirmed_at !== null
                && $request->session()->get('auth.two_factor.user_id') === $user->id
                && $request->session()->get('auth.two_factor.auth_version') === $user->auth_version
                && is_int($verifiedAt) && $verifiedAt <= time()
                && $verifiedAt > time() - (int) config('session.lifetime', 120) * 60)) {
                $request->session()->forget('auth.two_factor');
                return redirect()->route('two-factor.challenge');
            }
        }

        view()->share('preview', $request->attributes->get('admin_preview', false));
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
