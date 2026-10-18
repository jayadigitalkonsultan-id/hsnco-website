<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class PublicSeoController extends Controller
{
    public function robots(): Response
    {
        $rules = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /activate',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /verify-email-otp',
            'Disallow: /recovery-codes',
            'Sitemap: '.$this->origin().'/sitemap.xml',
            '',
        ]);

        return response($rules, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function sitemap(): Response
    {
        $origin = $this->origin();
        $urls = collect(config('seo.public_routes', []))->map(function (string $name) use ($origin): ?string {
            $route = Route::getRoutes()->getByName($name);
            if (! $route instanceof RoutingRoute || $route->methods() === [] || ! in_array('GET', $route->methods(), true)) {
                return null;
            }

            $path = trim($route->uri(), '/');

            return $origin.($path === '' ? '/' : '/'.$path);
        })->filter()->values();

        return response()->view('frontend.sitemap-xml', compact('urls'), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function origin(): string
    {
        $candidate = rtrim((string) config('seo.canonical_url'), '/');
        $parts = parse_url($candidate);
        abort_unless(is_array($parts)
            && in_array($parts['scheme'] ?? null, ['https', 'http'], true)
            && filled($parts['host'] ?? null)
            && ! isset($parts['user']) && ! isset($parts['pass'])
            && ! isset($parts['query']) && ! isset($parts['fragment'])
            && (! isset($parts['path']) || trim($parts['path'], '/') === ''), 500);

        return $candidate;
    }
}
