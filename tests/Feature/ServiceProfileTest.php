<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_imported_service_profile_is_complete_from_published_cms_records(): void
    {
        app(\App\Services\Cms\ImportServiceProfiles::class)->run(true);
        $response = $this->get('/practice-areas')->assertOk();
        foreach (config('services.introduction') as $paragraph) {
            $response->assertSee($paragraph);
        }
        foreach (config('services.groups') as $group) {
            $response->assertSee($group['title']);
            foreach ($group['items'] as $item) {
                $response->assertSee($item);
            }
        }
        $response->assertDontSee('Bidang praktik akan ditampilkan setelah konten disetujui')
            ->assertDontSee('pidana pidana')
            ->assertSee('Mulai percakapan');
    }
}
