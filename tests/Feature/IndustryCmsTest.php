<?php

namespace Tests\Feature;

use App\Models\Industry;
use App\Models\Media;
use App\Models\Professional;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustryCmsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): void
    {
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'secret' => null, 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => ['user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time()]]);
    }

    private function import(): Industry
    {
        $this->artisan('hsnco:import-industries', ['--publish' => true])->assertExitCode(0);

        return Industry::firstOrFail();
    }

    private function payload(Industry $industry, array $ids): array
    {
        return ['name' => $industry->name, 'slug' => $industry->slug, 'summary' => 'Ringkasan hasil penyuntingan', 'body' => 'Industry overview dari CMS', 'focus_text' => "Transaksi\nKepatuhan", 'key_contact_ids' => $ids, 'sort_order' => 0, 'status' => 'published'];
    }

    public function test_published_save_updates_image_content_contacts_and_modal_immediately(): void
    {
        $industry = $this->import();
        $this->administrator();
        $person = Professional::factory()->create(['name' => 'Professional Contact Uji', 'status' => 'published', 'published_at' => now()->subMinute()]);
        $image = Media::factory()->create(['disk' => 'frontend', 'path' => 'images/industry-updated.png']);
        $this->get(route('admin.industries.index'))->assertOk()->assertSee($industry->name);
        $this->get(route('admin.industries.edit', $industry))->assertOk()->assertSee('Key Contact')->assertSee($person->name);
        $this->put(route('admin.industries.update', $industry), $this->payload($industry, [$person->id]) + ['image_id' => $image->id])->assertSessionHasNoErrors()->assertRedirect();
        $url = route('industries.show', $industry->slug);
        $this->assertSame(1, $industry->keyContacts()->count());
        $this->get($url)->assertOk()->assertSee($person->name)->assertSee('Ringkasan hasil penyuntingan')->assertSee('data-profile="'.$person->slug.'"', false)->assertSee('id="detailModal"', false)->assertSee('images/industry-updated.png')->assertSee('Transaksi')->assertSee('Kepatuhan');
        $this->get(route('admin.industries.edit', $industry))->assertOk()->assertSee('Gambar industri')
            ->assertSee('value="'.$image->id.'" checked', false)->assertSee('value="published" selected', false);
        $this->put(route('admin.industries.update', $industry), array_replace($this->payload($industry, []), ['body' => 'Gambaran versi terbaru']))->assertSessionHasNoErrors();
        $this->assertSame(0, $industry->keyContacts()->count());
        $this->assertSame([], $industry->fresh()->getPublishedRevision()->payload['key_contact_ids']);
        $this->get($url)->assertDontSee($person->name)->assertSee('Gambaran versi terbaru')->assertDontSee('Industry overview dari CMS');
        $this->get(route('admin.industries.edit', $industry))->assertOk()->assertSee('Gambaran versi terbaru')->assertDontSee('Industry overview dari CMS');
    }

    public function test_unpublished_contact_cannot_be_selected_and_archived_contact_is_hidden(): void
    {
        $industry = $this->import();
        $this->administrator();
        $draft = Professional::factory()->create(['status' => 'draft', 'published_at' => null]);
        $this->put(route('admin.industries.update', $industry), $this->payload($industry, [$draft->id]))->assertSessionHasErrors('key_contact_ids.0');
        $person = Professional::factory()->create(['name' => 'Contact yang diarsipkan', 'status' => 'published', 'published_at' => now()->subMinute()]);
        $industry->keyContacts()->attach($person);
        $this->get(route('industries.show', $industry->slug))->assertSee($person->name);
        $person->update(['status' => 'archived']);
        $this->get(route('industries.show', $industry->slug))->assertDontSee($person->name);
    }

    public function test_import_preserves_edits_and_unpublished_sectors_are_not_exposed(): void
    {
        $industry = $this->import();
        $industry->update(['summary' => 'Suntingan dipertahankan', 'status' => 'draft']);
        $this->artisan('hsnco:import-industries', ['--publish' => true])->assertExitCode(0);
        $this->assertSame(11, Industry::count());
        $this->assertSame('Suntingan dipertahankan', $industry->fresh()->summary);
        $this->get(route('industries.show', $industry->slug))->assertNotFound();
        $this->get('/industries')->assertDontSee(route('industries.show', $industry->slug));
        $this->get('/')->assertDontSee(route('industries.show', $industry->slug));
        config(['seo.canonical_url' => 'https://hsncoatlaw.com']);
        $this->get('/sitemap.xml')->assertDontSee('https://hsncoatlaw.com/industries/'.$industry->slug);
    }

    public function test_guest_cannot_manage_industries(): void
    {
        $this->post(route('admin.industries.store'), ['name' => 'Tidak sah'])->assertRedirect(route('login'));
        $this->assertSame(0,Industry::count());
    }

    public function test_forms_have_two_statuses_and_published_creation_and_draft_control_public_routes(): void
    {
        $this->administrator();
        foreach (['admin.industries.index', 'admin.industries.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('value="draft"', false)->assertSee('value="published"', false)
                ->assertDontSee('value="review"', false)->assertDontSee('value="archived"', false);
        }
        $data = ['name' => 'Industri langsung tayang', 'slug' => 'industri-langsung-tayang'];
        foreach (['review', 'archived'] as $status) {
            $this->post(route('admin.industries.store'), $data + ['status' => $status])->assertSessionHasErrors('status');
        }
        $this->post(route('admin.industries.store'), $data + ['status' => 'published'])->assertSessionHasNoErrors()->assertRedirect();
        $industry = Industry::sole();
        $this->assertNotNull($industry->getPublishedRevision());
        $url = route('industries.show', $industry->slug);
        $this->get($url)->assertOk()->assertSee($industry->name)->assertSee('Explore the industry expertise and legal services');
        foreach (['/', '/industries', '/sitemap.xml'] as $listing) {
            $this->get($listing)->assertOk()->assertSee('/industries/'.$industry->slug);
        }
        $this->get(route('admin.industries.edit', $industry))->assertOk()->assertSee('Gambar industri')
            ->assertSee('name="image_id"', false)->assertDontSee('Workflow editorial');
        $this->put(route('admin.industries.update', $industry), $data + ['status' => 'draft'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($industry->fresh()->published_at);
        $this->get($url)->assertNotFound();
        foreach (['/', '/industries', '/sitemap.xml'] as $listing) {
            $this->get($listing)->assertOk()->assertDontSee('/industries/'.$industry->slug);
        }
        $this->post(route('admin.industries.publish', $industry))->assertSessionHasNoErrors()->assertRedirect();
        $this->get($url)->assertOk()->assertSee($industry->name);
    }

    public function test_editor_can_save_drafts_but_cannot_publish_or_hide_live_industries(): void
    {
        $editor = User::factory()->active('editor')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $editor->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->actingAs($editor)->withSession(['auth.two_factor' => ['user_id' => $editor->id, 'auth_version' => $editor->auth_version, 'verified_at' => time()]]);
        $data = ['name' => 'Industri editor', 'slug' => 'industri-editor'];
        $this->post(route('admin.industries.store'), $data + ['status' => 'published'])->assertForbidden();
        $this->assertSame(0, Industry::count());
        $this->post(route('admin.industries.store'), $data + ['status' => 'draft'])->assertSessionHasNoErrors()->assertRedirect();
        $industry = Industry::sole();
        $this->assertSame($editor->id, $industry->created_by);
        $industry->update(['status' => 'published', 'published_at' => now()]);
        $this->put(route('admin.industries.update', $industry), $data + ['status' => 'draft'])->assertForbidden();
        $this->assertSame('published', $industry->fresh()->status->value);
    }

    public function test_contacts_are_rechecked_before_publishing_and_do_not_block_hiding_industry(): void
    {
        $this->administrator();
        $contact = Professional::factory()->create(['status' => 'published', 'published_at' => now()]);
        $data = ['name' => 'Industri kontak', 'slug' => 'industri-kontak', 'status' => 'draft', 'key_contact_ids' => [$contact->id]];
        $this->post(route('admin.industries.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $industry = Industry::sole();
        $contact->update(['status' => 'draft']);
        $this->post(route('admin.industries.publish', $industry))->assertSessionHasErrors('key_contact_ids');
        $this->assertSame('draft', $industry->fresh()->status->value);
        $contact->update(['status' => 'published']);
        $this->post(route('admin.industries.publish', $industry))->assertSessionHasNoErrors()->assertRedirect();
        $contact->update(['status' => 'draft']);
        $this->post(route('admin.industries.archive', $industry))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('draft', $industry->fresh()->status->value);
        $this->assertSame(1, $industry->keyContacts()->count());
    }

    public function test_migration_preserves_published_industries_and_revision_history(): void
    {
        foreach (['review', 'archived', 'published'] as $status) {
            $industry = Industry::create(['name' => 'Industri '.$status, 'slug' => 'industri-'.$status, 'status' => $status]);
            $industry->revisions()->create(['version' => 1, 'status' => $status, 'payload' => $industry->attributesToArray()]);
        }
        $migration = require database_path('migrations/2026_11_02_000004_simplify_industry_statuses.php');
        $migration->up();
        $this->assertSame(2, Industry::where('status', 'draft')->count());
        $this->assertSame(1, Industry::where('status', 'published')->count());
        foreach (Industry::all() as $industry) {
            $this->assertSame(1, $industry->revisions()->count());
        }
    }
}
