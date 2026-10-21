<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Services\Cms\EnglishCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnglishFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_use_english_and_client_heading_has_no_arrow(): void
    {
        foreach (['/', '/about', '/practice-areas', '/industries', '/team', '/billing-fees', '/clients', '/insights', '/contact', '/sitemap'] as $url) {
            $this->get($url)->assertOk()->assertSee('<html lang="en">', false)->assertSee('Practice Areas')->assertSee('Professionals')->assertDontSee('Langsung ke konten');
        }
        $view = $this->view('frontend.partials.clients', ['clients' => collect()]);
        $view->assertDontSee('clients-title-arrow', false);
        $this->assertStringNotContainsString('clients-title-arrow', file_get_contents(resource_path('views/frontend/partials/clients.blade.php')));
    }

    public function test_dry_run_preserves_data_and_apply_is_idempotent_with_history_and_pending_draft(): void
    {
        Storage::fake('local');
        $practice = PracticeArea::factory()->create(['name' => 'Perdata & Komersial', 'slug' => 'perdata-komersial', 'summary' => 'Transaksi, investasi, dan kegiatan usaha.', 'status' => 'published', 'published_at' => now()->subMinute(), 'published_revision' => 1]);
        $practice->revisions()->create(['version' => 1, 'status' => 'published', 'payload' => $practice->attributesToArray()]);
        $draft = array_replace($practice->attributesToArray(), ['summary' => 'Hubungan kerja, kepatuhan, dan mobilitas tenaga kerja.', 'status' => 'review']);
        $practice->revisions()->create(['version' => 2, 'status' => 'review', 'payload' => $draft]);
        $this->artisan('hsnco:translate-frontend-en')->assertSuccessful();
        $this->assertSame('Perdata & Komersial', $practice->fresh()->name);
        $this->assertCount(0, Storage::disk('local')->allFiles());
        $this->artisan('hsnco:translate-frontend-en', ['--apply' => true])->assertSuccessful();
        $practice->refresh();
        $this->assertSame('Civil & Commercial', $practice->name);
        $this->assertSame('perdata-komersial', $practice->slug);
        $this->assertSame('published', $practice->status->value);
        $this->assertSame(3, $practice->published_revision);
        $this->assertSame('Perdata & Komersial', $practice->revisions()->where('version', 1)->first()->payload['name']);
        $latest = $practice->revisions()->orderByDesc('version')->first();
        $this->assertSame('review', $latest->status->value);
        $this->assertSame('Employment relations, compliance and workforce mobility.', $latest->payload['summary']);
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $count = $practice->revisions()->count();
        $this->artisan('hsnco:translate-frontend-en', ['--apply' => true])->assertSuccessful();
        $this->assertSame($count, $practice->revisions()->count());
    }

    public function test_approved_biographies_translate_with_crlf_without_changing_identity(): void
    {
        $items = json_decode(file_get_contents(database_path('seeders/data/professionals.json')), true);
        foreach ($items as $item) {
            $english = app(EnglishCopy::class)->text(str_replace("\n", "\r\n", $item['biography']));
            $this->assertNotSame($item['biography'], $english);
            $this->assertStringNotContainsString(' adalah ', $english);
            $this->assertSame($english, app(EnglishCopy::class)->text($english));
        }
        Storage::fake('local');
        $person = Professional::factory()->create(['name' => $items[0]['name'], 'biography' => $items[0]['biography']]);
        $this->artisan('hsnco:translate-frontend-en', ['--apply' => true])->assertSuccessful();
        $this->assertSame($items[0]['name'], $person->fresh()->name);
        $this->assertStringStartsWith('Hasbullah is an advocate', $person->fresh()->biography);
    }

    public function test_structured_copy_preserves_urls_identifiers_evidence_and_custom_content(): void
    {
        $original = ['slug' => 'ketenagakerjaan-dan-imigrasi', 'button_url' => '/practice-areas#pidana', 'image_id' => (string) Str::ulid(), 'client_name' => 'Bank Negara Indonesia', 'source_text' => 'Catatan sumber Bahasa Indonesia', 'evidence' => 'Bukti verifikasi', 'custom' => 'A bespoke editorial note.', 'heading' => 'Mulai percakapan'];
        $translated = app(EnglishCopy::class)->structured($original);
        foreach (array_keys($original) as $key) {
            if ($key !== 'heading') {
                $this->assertSame($original[$key], $translated[$key]);
            }
        }
        $this->assertSame('Start a conversation', $translated['heading']);
        $this->assertSame('formerly Bank Internasional Indonesia', app(EnglishCopy::class)->text('formerly Bank Internasional Indonesia'));
    }

    public function test_page_sections_translate_without_changing_visibility_or_relations(): void
    {
        Storage::fake('local');
        $page = Page::factory()->create(['key' => 'home', 'slug' => 'home', 'title' => 'Penasihat Hukum Strategis']);
        $section = $page->sections()->create(['key' => 'consultation', 'type' => 'enquiry', 'heading' => 'Keahlian mendalam.Arah yang jelas.', 'content' => ['eyebrow' => 'Mulai percakapan', 'button_url' => '/contact#enquiry'], 'is_visible' => false, 'sort_order' => 3]);
        $this->artisan('hsnco:translate-frontend-en', ['--apply' => true])->assertSuccessful();
        $section->refresh();
        $this->assertSame('Start a conversation', $section->content['eyebrow']);
        $this->assertFalse($section->is_visible);
        $this->assertSame('/contact#enquiry', $section->content['button_url']);
        $this->assertSame($page->id, $section->page_id);
    }
}
