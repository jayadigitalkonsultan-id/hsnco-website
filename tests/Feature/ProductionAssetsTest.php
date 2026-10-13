<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class ProductionAssetsTest extends TestCase
{
    private function configureProductionOrigin(?string $assetOrigin = null): void
    {
        $this->app['env'] = 'production';
        config(['app.env' => 'production', 'app.url' => 'https://hsncoatlaw.com', 'app.asset_url' => $assetOrigin]);
        (new AppServiceProvider($this->app))->boot();
    }

    public function test_assets_use_the_production_https_origin_without_proxy_headers(): void
    {
        $this->configureProductionOrigin();
        foreach (['/', '/about', '/practice-areas', '/team', '/insights', '/contact', '/sitemap'] as $path) {
            $html = $this->get('http://internal-service:8080'.$path)->assertOk()->getContent();
            preg_match_all('~(?:href|src)="([^"]*/assets/[^"]*)"~', $html, $matches);
            $this->assertNotEmpty($matches[1]);
            foreach ($matches[1] as $url) {
                $this->assertStringStartsWith('https://hsncoatlaw.com/assets/', $url);
                $relative = ltrim(parse_url($url, PHP_URL_PATH), '/');
                $this->assertFileExists(public_path($relative));
            }
            $this->assertSame(1, substr_count($html, 'bootstrap.min.css'));
            $this->assertSame(1, substr_count($html, 'bootstrap.bundle.min.js'));
            $this->assertStringNotContainsString('http://hsncoatlaw.com', $html);
            $this->assertStringNotContainsString(':5173', $html);
        }
    }

    public function test_explicit_asset_origin_works_without_changing_navigation_origin(): void
    {
        $this->configureProductionOrigin('https://static.example.test');
        $html = $this->get('http://internal-service:8080/')->assertOk()->getContent();
        $this->assertStringContainsString('https://static.example.test/assets/css/style.css', $html);
        $this->assertStringContainsString('https://hsncoatlaw.com/about', $html);
    }

    public function test_local_http_assets_are_not_forced_to_production(): void
    {
        $this->get('http://localhost/')->assertOk()
            ->assertSee('http://localhost/assets/css/style.css', false)
            ->assertDontSee('https://hsncoatlaw.com/assets/', false);
    }

    public function test_asset_manifest_and_css_references_exist(): void
    {
        $manifest = json_decode(file_get_contents(base_path('docker/assets-manifest.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($manifest['files'] as $entry) {
            $file = public_path('assets/'.$entry['file']);
            $this->assertFileExists($file);
            $this->assertSame($entry['sha256'], hash_file('sha256', $file));
            if (str_ends_with($file, '.css')) {
                preg_match_all('~url\(\s*[\'"]?([^\s)\'"]+)[\'"]?\s*\)~', file_get_contents($file), $references);
                foreach ($references[1] as $reference) {
                    if (preg_match('~^(data:|https?:|\#)~', $reference)) {
                        continue;
                    }
                    $this->assertFileExists(dirname($file).DIRECTORY_SEPARATOR.$reference);
                }
            }
        }
    }
}