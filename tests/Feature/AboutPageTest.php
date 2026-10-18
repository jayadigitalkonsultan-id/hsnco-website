<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Professional;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Database\Seeders\FrontendContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create([
            'user_id' => $user->id, 'method' => 'email', 'secret' => null, 'confirmed_at' => now(),
        ]);
        $this->actingAs($user)->withSession(['auth.two_factor' => [
            'user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time(),
        ]]);

        return $user;
    }

    private function page(string $status = 'published'): Page
    {
        $page = Page::factory()->create([
            'key' => 'about', 'slug' => 'about', 'title' => 'Tentang Firma', 'template' => 'about',
            'status' => $status, 'published_at' => $status === 'published' ? now()->subMinute() : null,
        ]);
        foreach (config('about.sections') as $key => $section) {
            $page->sections()->create($section + ['key' => $key, 'sort_order' => $page->sections()->count() + 1, 'is_visible' => true]);
        }

        return $page;
    }

    private function replaceSection(Page $page, string $key, array $content): void
    {
        $page->sections()->where('key', $key)->firstOrFail()->update(['content' => $content]);
    }

    public function test_all_nine_sections_round_trip_through_draft_preview_and_publication(): void
    {
        $this->administrator();
        $page = $this->page('draft');
        $logo = Media::factory()->create(['path' => 'images/approved-client-logo.jpg']);
        $photo = Media::factory()->create(['path' => 'images/approved-leader.jpg']);
        $leader = Professional::factory()->create([
            'status' => 'published', 'published_at' => now()->subMinute(), 'photo_id' => $photo->id,
            'name' => 'Pimpinan Terbit', 'biography' => '<p>Biografi pimpinan dari modul Profesional.</p>',
        ]);
        $edits = [
            'banner' => ['eyebrow' => 'Profil firma', 'breadcrumb_label' => 'Tentang HSNCO', 'description' => 'Pengantar dari CMS'],
            'introduction' => ['column_one' => '<p>Narasi kolom pertama.</p>', 'column_two' => '<p>Narasi kolom kedua.</p>'],
            'quote' => ['quote' => '<p>Kejelasan untuk setiap keputusan.</p>', 'attribution' => 'Pernyataan resmi firma', 'approved' => true, 'evidence' => 'INTERNAL-QUOTE-PROOF'],
            'history' => ['eyebrow' => 'Perjalanan kami', 'items' => [
                ['year' => '2020', 'title' => 'Milestone pertama dalam urutan editorial', 'description' => 'Riwayat yang disetujui.', 'verified' => true, 'evidence' => 'INTERNAL-HISTORY-PROOF'],
                ['year' => '2010', 'title' => 'Milestone kedua dalam urutan editorial', 'description' => 'Riwayat lainnya.', 'verified' => true, 'evidence' => 'INTERNAL-HISTORY-PROOF'],
            ]],
            'vision-mission' => ['vision_title' => 'Judul visi dari CMS', 'vision' => 'Penjelasan visi.', 'mission_title' => 'Judul misi dari CMS', 'mission' => 'Penjelasan misi.'],
            'statistics' => ['items' => [['value' => '12', 'unit' => 'orang', 'title' => 'Jumlah terverifikasi', 'evidence' => 'INTERNAL-NUMBER-PROOF', 'verified_at' => today()->toDateString(), 'verified' => true]]],
            'clients' => ['items' => [['client_name' => 'Klien Berizin', 'media_id' => $logo->id, 'permission_confirmed' => true, 'evidence' => 'INTERNAL-CLIENT-PROOF', 'feedback' => 'Tanggapan yang telah diizinkan.', 'rating' => '5/5']]],
            'leadership' => ['eyebrow' => 'Pimpinan', 'professional_ids' => [$leader->id]],
            'closing-cta' => ['button_label' => 'Konsultasi dengan firma', 'button_url' => '/contact#enquiry'],
        ];
        $this->get(route('admin.pages.edit', $page))->assertOk();
        foreach ($page->sections as $section) {
            $this->put(route('admin.pages.sections.update', [$page, $section->id]), [
                'key' => $section->key, 'type' => $section->type,
                'heading' => $section->key === 'banner' ? 'Heading banner dari CMS' : $section->heading,
                'content' => $edits[$section->key], 'sort_order' => $section->sort_order, 'is_visible' => true,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Narasi kolom pertama.');
        $this->get('/about')->assertOk()->assertDontSee('Heading banner dari CMS')->assertDontSee('Pimpinan Terbit');
        $preview = $this->get(route('admin.pages.preview', $page))->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Heading banner dari CMS')->assertSee('Biografi pimpinan dari modul Profesional.')
            ->assertDontSee('INTERNAL-CLIENT-PROOF');
        $this->assertStringContainsString('no-store', $preview->headers->get('Cache-Control'));

        $this->put(route('admin.pages.update', $page), [
            'title' => 'Tentang Firma', 'slug' => 'about', 'key' => 'about', 'template' => 'about', 'status' => 'review',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('admin.pages.publish', $page))->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/about')->assertOk()->assertSeeInOrder([
            'Heading banner dari CMS', 'Narasi kolom pertama.', 'Narasi kolom kedua.',
            'Kejelasan untuk setiap keputusan.', 'Milestone pertama dalam urutan editorial',
            'Milestone kedua dalam urutan editorial', 'Judul visi dari CMS', 'Judul misi dari CMS',
            'Jumlah terverifikasi', 'Klien Berizin', 'Pimpinan Terbit', 'Konsultasi dengan firma',
        ])->assertSee('Tentang HSNCO')->assertSee('images/approved-client-logo.jpg')->assertSee('images/approved-leader.jpg')
            ->assertSee('href="/contact#enquiry"', false)->assertDontSee('INTERNAL-');

        $intro = $page->sections()->where('key', 'introduction')->firstOrFail();
        $this->put(route('admin.pages.sections.update', [$page, $intro->id]), [
            'key' => 'introduction', 'type' => 'about_intro', 'heading' => $intro->heading,
            'content' => ['column_one' => 'EDIT-BELUM-DITERBITKAN', 'column_two' => 'Kolom kedua'],
        ])->assertSessionHasNoErrors();
        $this->get('/about')->assertOk()->assertDontSee('EDIT-BELUM-DITERBITKAN')->assertSee('Narasi kolom pertama.');
        $this->get(route('admin.pages.preview', $page))->assertOk()->assertSee('EDIT-BELUM-DITERBITKAN');
    }

    public function test_public_claims_require_proof_permission_valid_dates_and_published_relations(): void
    {
        $page = $this->page();
        $public = Media::factory()->create(['path' => 'images/permitted-logo.jpg']);
        $private = Media::factory()->create(['path' => 'internal/private-logo.jpg', 'visibility' => 'private']);
        $this->replaceSection($page, 'quote', ['quote' => 'UNAPPROVED-QUOTE', 'attribution' => 'Firm', 'approved' => false, 'evidence' => 'INTERNAL-PROOF']);
        $this->replaceSection($page, 'history', ['items' => [
            ['title' => 'UNVERIFIED-HISTORY', 'year' => '2000', 'description' => 'Draft', 'verified' => false, 'evidence' => 'INTERNAL-PROOF'],
            ['title' => 'PROOFLESS-HISTORY', 'year' => '2001', 'description' => 'Draft', 'verified' => true],
        ]]);
        $this->replaceSection($page, 'statistics', ['items' => [
            ['value' => '9999', 'title' => 'UNVERIFIED-NUMBER', 'verified' => false],
            ['value' => '4', 'title' => 'BAD-DATE-NUMBER', 'verified' => true, 'evidence' => 'INTERNAL-PROOF', 'verified_at' => '2026-02-30'],
            ['value' => '4', 'title' => 'FUTURE-DATE-NUMBER', 'verified' => true, 'evidence' => 'INTERNAL-PROOF', 'verified_at' => today()->addDay()->toDateString()],
        ]]);
        $this->replaceSection($page, 'clients', ['items' => [
            ['client_name' => 'UNPERMITTED-CLIENT', 'media_id' => $public->id, 'permission_confirmed' => false, 'evidence' => 'INTERNAL-PROOF'],
            ['client_name' => 'PROOFLESS-CLIENT', 'media_id' => $public->id, 'permission_confirmed' => true],
            ['client_name' => 'PRIVATE-LOGO-CLIENT', 'media_id' => $private->id, 'permission_confirmed' => true, 'evidence' => 'INTERNAL-PROOF'],
            ['client_name' => 'PERMITTED-CLIENT', 'media_id' => $public->id, 'permission_confirmed' => true, 'evidence' => 'INTERNAL-PROOF', 'feedback' => '<script>unsafeFeedback()</script>'],
        ]]);
        $leader = Professional::factory()->create(['name' => 'DRAFT-LEADER', 'photo_id' => $private->id]);
        $this->replaceSection($page, 'leadership', ['professional_ids' => [$leader->id]]);
        $response = $this->get('/about')->assertOk()->assertSee('PERMITTED-CLIENT')->assertSee('images/permitted-logo.jpg')
            ->assertSee('&lt;script&gt;unsafeFeedback()', false)->assertDontSee('<script>unsafeFeedback()', false)
            ->assertDontSee('INTERNAL-PROOF')->assertDontSee('internal/private-logo.jpg')->assertDontSee('DRAFT-LEADER')
            ->assertDontSee('UNAPPROVED-QUOTE')->assertDontSee('UNVERIFIED-HISTORY')->assertDontSee('PROOFLESS-HISTORY')
            ->assertDontSee('UNVERIFIED-NUMBER')->assertDontSee('BAD-DATE-NUMBER')->assertDontSee('FUTURE-DATE-NUMBER')
            ->assertDontSee('UNPERMITTED-CLIENT')->assertDontSee('PROOFLESS-CLIENT')->assertDontSee('PRIVATE-LOGO-CLIENT');
        $this->assertStringNotContainsString('1998', $response->getContent());

        $leader->update(['status' => 'published', 'published_at' => now()->subMinute()]);
        $this->get('/about')->assertOk()->assertSee('DRAFT-LEADER')->assertDontSee('internal/private-logo.jpg');
        $leader->update(['status' => 'archived']);
        $this->get('/about')->assertOk()->assertDontSee('DRAFT-LEADER');
        $page->update(['status' => 'archived']);
        $this->get('/about')->assertOk()->assertDontSee('PERMITTED-CLIENT');
    }

    public function test_verification_flags_cannot_be_saved_without_evidence_and_dates(): void
    {
        $this->administrator();
        $page = $this->page('draft');
        $cases = [
            ['quote', ['quote' => 'Kutipan', 'attribution' => 'Firm', 'approved' => true], ['content.evidence']],
            ['history', ['items' => [['title' => 'Milestone', 'verified' => true]]], ['content.items.0.year', 'content.items.0.description', 'content.items.0.evidence']],
            ['statistics', ['items' => [['value' => '25', 'title' => 'Tim', 'verified' => true]]], ['content.items.0.evidence', 'content.items.0.verified_at']],
            ['statistics', ['items' => [['value' => '25', 'title' => 'Tim', 'verified' => true, 'evidence' => 'File', 'verified_at' => today()->addDay()->toDateString()]]], ['content.items.0.verified_at']],
            ['clients', ['items' => [['client_name' => 'Klien', 'permission_confirmed' => true]]], ['content.items.0.evidence']],
        ];
        foreach ($cases as [$key, $content, $errors]) {
            $section = $page->sections()->where('key', $key)->firstOrFail();
            $this->put(route('admin.pages.sections.update', [$page, $section->id]), [
                'key' => $key, 'type' => $section->type, 'heading' => $section->heading, 'content' => $content,
            ])->assertSessionHasErrors($errors);
        }
        $this->assertSame(0, $page->revisions()->count());
    }

    public function test_legacy_sections_are_editable_without_overwriting_the_saved_content(): void
    {
        $this->administrator();
        $page = $this->page('draft');
        $intro = $page->sections()->where('key', 'introduction')->firstOrFail();
        $intro->update(['type' => 'rich_text', 'content' => ['body' => 'Narasi lama milik editor', 'source_text' => 'Sumber lama']]);
        $quote = $page->sections()->where('key', 'quote')->firstOrFail();
        $quote->update(['type' => 'rich_text', 'content' => ['source_text' => 'Kutipan lama belum disetujui']]);
        $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Narasi lama milik editor')
            ->assertSee('name="type" value="about_intro"', false)->assertSee('name="type" value="quote"', false);
        $this->assertSame('rich_text', $intro->fresh()->type);
        $this->assertSame('Narasi lama milik editor', $intro->fresh()->content['body']);
    }

    public function test_seed_uses_structured_fields_and_keeps_unverified_claims_unpublished(): void
    {
        $this->seed(FrontendContentSeeder::class);
        $page = Page::where('key', 'about')->firstOrFail();
        $this->assertSame('draft', $page->status->value);
        $this->assertCount(9, $page->sections);
        $this->assertSame('quote', $page->sections->firstWhere('key', 'quote')->type);
        $this->assertSame([], $page->sections->firstWhere('key', 'statistics')->content['items']);
        $page->sections()->where('key', 'closing-cta')->update(['heading' => 'Perubahan editor']);
        $this->seed(FrontendContentSeeder::class);
        $this->assertSame('Perubahan editor', $page->sections()->where('key', 'closing-cta')->firstOrFail()->heading);
        $this->get('/about')->assertOk()->assertDontSee('Perubahan editor')->assertDontSee('Profil pimpinan');
        $this->get(route('admin.pages.preview', $page))->assertRedirect(route('login'));
    }
}
