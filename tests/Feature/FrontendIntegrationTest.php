<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendIntegrationTest extends TestCase
{
    public function test_all_frontend_routes_render_with_shared_layout_and_local_assets(): void
    {
        foreach (['home', 'about', 'practice-areas', 'team', 'insights', 'contact', 'sitemap'] as $name) {
            $response = $this->get(route($name));
            $response->assertOk()->assertSee('id="main"', false)->assertSee('site-header')->assertSee('site-footer');
            $response->assertSee('assets/vendor/bootstrap/css/bootstrap.min.css', false);
            $response->assertSee('assets/vendor/bootstrap/js/bootstrap.bundle.min.js', false);
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, 'bootstrap.bundle.min.js'));
            $this->assertSame(1, preg_match_all('/<h1\b/', $html));
            $this->assertStringNotContainsString('.html"', $html);
            $this->assertStringNotContainsString('@vite', $html);
            $this->assertStringNotContainsString('@endsection', $html);
            $this->assertStringContainsString('— HSNCO</title>', $html);
            $this->assertStringNotContainsString('localhost:5173', $html);
        }
    }

    public function test_modals_are_single_instances_and_image_base_is_generated_by_laravel(): void
    {
        foreach (['practice-areas', 'team', 'insights'] as $name) {
            $html = $this->get(route($name))->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, 'id="detailModal"'));
            $this->assertStringContainsString('data-asset-base="'.asset('assets').'/"', $html);
        }
    }

    public function test_page_scoped_plugins_and_contact_demo_contract_are_preserved(): void
    {
        $this->get(route('home'))->assertDontSee('jquery.min.js')->assertDontSee('jstree.min.js');
        $this->get(route('sitemap'))->assertSee('jquery.min.js')->assertSee('jstree.min.js');
        $this->get(route('contact'))->assertSee('name="_token"', false)->assertSee('data-demo-form', false);
        $this->post(route('contact'))->assertStatus(405);
        $this->get('/up')->assertOk();
    }
}