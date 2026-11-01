<?php

namespace Tests\Feature;

use App\Models\PracticeArea;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use App\Services\Cms\ImportServiceProfiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeServiceCmsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): void
    {
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'secret' => null, 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => ['user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time()]]);
    }

    public function test_import_is_repeatable_and_does_not_overwrite_editorial_changes(): void
    {
        $importer = app(ImportServiceProfiles::class);
        $this->assertSame(8, $importer->run(true));
        $record = PracticeArea::firstOrFail();
        $record->update(['summary' => 'Ringkasan yang telah disunting']);
        $this->assertSame(0, $importer->run(true));
        $this->assertSame(8, PracticeArea::count());
        $this->assertSame('Ringkasan yang telah disunting', $record->fresh()->summary);
        $this->assertNotNull($record->publicImage);
        $this->assertSame(1, $record->revisions()->count());
    }

    public function test_saving_draft_hides_practice_and_saving_published_updates_frontend_immediately(): void
    {
        app(ImportServiceProfiles::class)->run(true);
        $this->administrator();
        $practice = PracticeArea::where('slug', 'ketenagakerjaan-dan-imigrasi')->firstOrFail();
        $image = PracticeArea::where('slug', 'pidana')->firstOrFail()->image_id;
        $this->get(route('admin.practice-areas.index'))->assertOk()->assertSee($practice->name);
        $this->get(route('admin.practice-areas.edit', $practice))->assertOk()->assertSee('Daftar layanan')->assertSee('Gambar latar kategori');
        $this->put(route('admin.practice-areas.update', $practice), [
            'name' => 'Legal tenaga kerja diperbarui', 'slug' => $practice->slug,
            'summary' => 'Ringkasan panel dari CMS', 'body' => "Butir layanan pertama\r\nButir layanan kedua",
            'image_id' => $image, 'sort_order' => 0, 'status' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/practice-areas')->assertOk()->assertDontSee('Legal tenaga kerja diperbarui');
        $this->get('/contact')->assertOk()->assertDontSee('Legal tenaga kerja diperbarui');
        $this->get('/sitemap')->assertOk()->assertDontSee('Legal tenaga kerja diperbarui');
        $this->put(route('admin.practice-areas.update', $practice), [
            'name' => 'Legal tenaga kerja diperbarui', 'slug' => $practice->slug,
            'summary' => 'Ringkasan panel dari CMS', 'body' => "Butir layanan pertama\nButir layanan kedua",
            'image_id' => $image, 'sort_order' => 0, 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $response = $this->get('/practice-areas')->assertOk()->assertSee('Legal tenaga kerja diperbarui')
            ->assertSee('Ringkasan panel dari CMS')->assertSee('Butir layanan pertama')->assertSee('Butir layanan kedua');
        $response->assertSeeInOrder(['Legal tenaga kerja diperbarui', 'Civil &amp; Commercial'], false);
        $this->assertSame($image, $practice->fresh()->image_id);
        $this->get(route('admin.practice-areas.edit', $practice))->assertOk()
            ->assertSee('value="published" selected', false)->assertDontSee('Workflow editorial');
        $this->get('/contact')->assertOk()->assertSee('Legal tenaga kerja diperbarui');
        $this->get('/sitemap')->assertOk()->assertSee('Legal tenaga kerja diperbarui');
        $this->put(route('admin.practice-areas.update', $practice), [
            'name' => 'Versi layanan terkini', 'slug' => $practice->slug,
            'body' => 'Butir terbaru', 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/practice-areas')->assertOk()->assertSee('Versi layanan terkini')->assertSee('Butir terbaru')
            ->assertDontSee('Legal tenaga kerja diperbarui');
        $this->get(route('admin.practice-areas.edit', $practice))->assertOk()->assertSee('Versi layanan terkini')
            ->assertDontSee('Legal tenaga kerja diperbarui');
        $this->post(route('admin.practice-areas.archive', $practice))->assertRedirect();
        $this->assertSame('draft', $practice->fresh()->status->value);
        $this->get('/practice-areas')->assertOk()->assertDontSee('Versi layanan terkini');
    }

    public function test_admin_can_create_published_practice_and_forms_offer_only_two_statuses(): void
    {
        $this->administrator();
        foreach (['admin.practice-areas.index', 'admin.practice-areas.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('value="draft"', false)
                ->assertSee('value="published"', false)->assertDontSee('value="review"', false)
                ->assertDontSee('value="archived"', false);
        }
        $this->post(route('admin.practice-areas.store'), [
            'name' => 'Praktik langsung tayang', 'slug' => 'praktik-langsung-tayang', 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $practice = PracticeArea::sole();
        $this->assertNotNull($practice->published_at);
        $this->assertNotNull($practice->getPublishedRevision());
        $this->get('/practice-areas')->assertOk()->assertSee($practice->name);
    }

    public function test_old_statuses_are_rejected_and_editor_cannot_publish_or_hide_live_practice(): void
    {
        $this->administrator();
        foreach (['review', 'archived'] as $status) {
            $this->post(route('admin.practice-areas.store'), [
                'name' => 'Praktik', 'slug' => 'praktik-baru', 'status' => $status,
            ])->assertSessionHasErrors('status');
        }
        $this->assertSame(0, PracticeArea::count());
        $editor = User::factory()->active('editor')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $editor->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->actingAs($editor)->withSession(['auth.two_factor' => ['user_id' => $editor->id, 'auth_version' => $editor->auth_version, 'verified_at' => time()]]);
        $this->post(route('admin.practice-areas.store'), [
            'name' => 'Praktik editor', 'slug' => 'praktik-editor', 'status' => 'published',
        ])->assertForbidden();
        $this->assertSame(0, PracticeArea::count());
        $practice = PracticeArea::factory()->create([
            'created_by' => $editor->id, 'status' => 'published', 'published_at' => now(),
        ]);
        $this->put(route('admin.practice-areas.update', $practice), [
            'name' => $practice->name, 'slug' => $practice->slug, 'status' => 'draft',
        ])->assertForbidden();
        $this->assertSame('published', $practice->fresh()->status->value);
    }

    public function test_legacy_states_become_drafts_without_publishing_or_losing_history(): void
    {
        foreach (['review', 'archived'] as $status) {
            $practice = PracticeArea::factory()->create(['status' => $status]);
            $practice->revisions()->create(['version' => 1, 'status' => $status, 'payload' => $practice->attributesToArray()]);
        }
        $migration = require database_path('migrations/2026_11_01_000001_simplify_practice_area_statuses.php');
        $migration->up();
        $this->assertSame(2, PracticeArea::where('status', 'draft')->count());
        foreach (PracticeArea::all() as $practice) {
            $this->assertSame(1, $practice->revisions()->count());
            $this->get('/practice-areas')->assertOk()->assertDontSee($practice->name);
        }
    }

    public function test_unpublished_data_is_not_replaced_by_static_config(): void
    {
        app(ImportServiceProfiles::class)->run();
        $this->get('/practice-areas')->assertOk()->assertDontSee('id="service-group-1"', false)
            ->assertSee('Our practice area information is being updated');
        $this->assertSame(8, PracticeArea::where('status', 'draft')->count());
    }

    public function test_guest_cannot_change_service_data(): void
    {
        $this->post(route('admin.practice-areas.store'), ['name' => 'Unauthorized'])->assertRedirect(route('login'));
        $this->assertSame(0, PracticeArea::count());
    }

    public function test_super_admin_login_opens_an_editable_practice_even_with_local_preview_enabled(): void
    {
        $this->app['env'] = 'local';
        config(['app.env' => 'local', 'admin.local_preview' => true]);
        $user = User::factory()->active('super_admin')->create();
        UserTwoFactorAuthentication::factory()->create([
            'user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now(),
        ]);
        $practice = PracticeArea::factory()->create(['status' => 'draft']);

        $this->withSession(['_token' => str_repeat('a', 40)])
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password', '_token' => str_repeat('a', 40)])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.practice-areas.edit', $practice))->assertOk()
            ->assertViewHas('editable', true)->assertSee('Super Administrator')
            ->assertDontSee('<fieldset disabled', false)->assertDontSee('Pratinjau lokal');
        $this->put(route('admin.practice-areas.update', $practice), [
            'name' => $practice->name, 'slug' => $practice->slug, 'status' => 'published',
            '_token' => $this->app['session.store']->token(),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('published', $practice->fresh()->status->value);
    }
}
