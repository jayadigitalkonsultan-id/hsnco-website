<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PracticeArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeIndustriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('hsnco:import-industries', ['--publish' => true])->assertExitCode(0);
    }

    public function test_home_service_showcase_now_displays_eleven_industries(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('data-home-industries', false)->assertSee('Explore industries')->assertDontSee('data-home-practices', false);
        foreach (config('industries.items') as $industry) {
            $response->assertSee($industry['name'])->assertSee(route('industries.show', $industry['slug']));
        }
    }

    public function test_cms_showcase_uses_industries_and_respects_visibility(): void
    {
        $page = Page::factory()->create(['key' => 'home', 'slug' => 'home', 'template' => 'home', 'status' => 'published', 'published_at' => now()->subMinute()]);
        $section = $page->sections()->create(['key' => 'featured-practices', 'type' => 'practice_selection', 'heading' => 'Expertise', 'is_visible' => true, 'sort_order' => 1, 'content' => ['practice_area_ids' => []]]);
        $this->get('/')->assertOk()->assertSee('data-home-industries', false)->assertSee('Agribusiness');
        $section->update(['is_visible' => false]);
        $this->get('/')->assertOk()->assertDontSee('data-home-industries', false);
    }

    public function test_industry_index_and_every_detail_page_resolve_with_local_images(): void
    {
        $this->get(route('industries'))->assertOk()->assertSee('Diverse industries.');
        foreach (config('industries.items') as $industry) {
            $this->assertFileExists(public_path('assets/images/'.$industry['image']));
            $this->get(route('industries.show', $industry['slug']))->assertOk()->assertSee($industry['name'])->assertSee($industry['overview'])->assertSee(route('contact').'#enquiry');
        }
        $this->get('/industries/tidak-tersedia')->assertNotFound();
    }

    public function test_practice_areas_remain_a_separate_published_cms_directory(): void
    {
        $practice = PracticeArea::factory()->create(['name' => 'Legal terbit HSNCO', 'slug' => 'layanan-hsnco', 'status' => 'published', 'published_at' => now()->subMinute()]);
        $this->get(route('practice-areas'))->assertOk()->assertSee($practice->name);
        $this->get(route('industries'))->assertOk()->assertDontSee('practice-directory-row');
    }

    public function test_sitemap_includes_industry_index_and_detail_urls(): void
    {
        config(['seo.canonical_url' => 'https://hsncoatlaw.com']);
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('https://hsncoatlaw.com/industries');
        foreach (config('industries.items') as $industry) {
            $response->assertSee('https://hsncoatlaw.com/industries/'.$industry['slug']);
        }
        $this->get('/sitemap')->assertOk()->assertSee(route('industries'));
    }
}
