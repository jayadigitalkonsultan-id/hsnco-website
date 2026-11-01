<?php
namespace Tests\Feature;

use App\Models\Client;
use App\Models\Media;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCmsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): void
    {
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'secret' => null, 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => ['user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time()]]);
    }

    public function test_client_import_preserves_all_names_and_is_repeatable(): void
    {
        $this->artisan('hsnco:import-clients', ['--publish' => true])->assertSuccessful();
        $this->assertSame(43, Client::count());
        $this->assertSame(8, Client::where('category', 'finance')->count());
        $this->assertSame(29, Client::where('category', 'corporate')->count());
        $this->assertSame(6, Client::where('category', 'institution')->count());
        $client = Client::first();$client->update(['summary' => 'Editorial preserved']);
        $this->artisan('hsnco:import-clients', ['--publish' => true])->assertSuccessful();
        $this->assertSame(43, Client::count());
        $this->assertSame('Editorial preserved', $client->fresh()->summary);
        $this->artisan('hsnco:import-client-logos')->assertSuccessful();
        $this->assertSame(7, Client::whereNotNull('image_id')->count());
        $this->artisan('hsnco:import-client-logos')->assertSuccessful();
        $this->assertSame(7, Client::whereNotNull('image_id')->count());
    }

    public function test_admin_saves_two_statuses_and_frontend_uses_latest_logo_and_publication(): void
    {
        $this->administrator();
        $this->get(route('admin.clients.index'))->assertOk()->assertSee('Klien dan slideshow website');
        $this->get(route('admin.clients.create'))->assertOk()->assertSee('Sektor klien')->assertSee('Logo klien');
        $logo = Media::factory()->create(['disk' => 'frontend', 'path' => 'images/logo-white.png', 'visibility' => 'public', 'caption' => 'dark-surface']);
        $this->post(route('admin.clients.store'), [
            'name' => 'Klien uji editorial', 'slug' => 'klien-uji-editorial', 'category' => 'finance',
            'body' => 'Penugasan yang disetujui', 'source_note' => 'INTERNAL-NOT-PUBLIC',
            'image_id' => $logo->id, 'status' => 'draft', 'sort_order' => 2,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $client = Client::where('slug', 'klien-uji-editorial')->firstOrFail();
        $this->get('/')->assertOk()->assertDontSee('Klien uji editorial');
        $this->post(route('admin.clients.publish', $client))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertOk()->assertSee('Klien uji editorial')->assertDontSee('Penugasan yang disetujui')
            ->assertSee('images/logo-white.png')->assertSee('assets/css/clients.css')->assertSee('assets/js/clients.js')->assertDontSee('INTERNAL-NOT-PUBLIC');
        $this->get('/about')->assertOk()->assertSee('Klien uji editorial')->assertSee('assets/css/clients.css')->assertSee('assets/js/clients.js');
        $this->get('/clients')->assertOk()->assertSee('Klien uji editorial')->assertSee('images/logo-white.png')->assertDontSee('INTERNAL-NOT-PUBLIC');
        $this->get(route('admin.clients.edit', $client))->assertOk()->assertSee('image-picker-contain')->assertSee('image-picker-card-dark')
            ->assertSee('value="'.$logo->id.'" checked', false)->assertSee('value="published" selected', false);
        $newLogo = Media::factory()->create(['disk' => 'frontend', 'path' => 'images/logo-updated.png']);
        $this->put(route('admin.clients.update', $client), ['name' => 'Revisi nama klien', 'slug' => $client->slug, 'category' => 'corporate', 'image_id' => $newLogo->id, 'status' => 'published'])->assertSessionHasNoErrors()->assertRedirect();
        foreach (['/', '/about', '/clients'] as $url) {
            $this->get($url)->assertOk()->assertSee('Revisi nama klien')->assertSee('images/logo-updated.png')->assertDontSee('Klien uji editorial')->assertDontSee('INTERNAL-NOT-PUBLIC');
        }
        $this->get(route('admin.clients.edit', $client))->assertOk()->assertSee('Revisi nama klien')->assertDontSee('Klien uji editorial');
        $this->assertSame($newLogo->id, $client->fresh()->getPublishedRevision()->payload['image_id']);
        $this->put(route('admin.clients.update', $client), ['name' => 'Revisi nama klien', 'slug' => $client->slug, 'category' => 'corporate', 'status' => 'draft'])->assertSessionHasNoErrors()->assertRedirect();
        foreach (['/', '/about', '/clients'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Revisi nama klien');
        }
        $this->post(route('admin.clients.publish', $client))->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('admin.clients.archive', $client))->assertRedirect();
        $this->assertSame('draft', $client->fresh()->status->value);
        $this->assertSame(6, $client->revisions()->count());
        $this->get('/')->assertDontSee('Revisi nama klien');
    }

    public function test_guest_writes_and_invalid_categories_are_rejected(): void
    {
        $this->post(route('admin.clients.store'), ['name' => 'Unauthorized'])->assertRedirect(route('login'));
        $this->administrator();
        $this->post(route('admin.clients.store'), ['name' => 'Invalid', 'slug' => 'invalid', 'category' => 'invalid'])->assertSessionHasErrors('category');
        $this->assertSame(0, Client::count());
    }

    public function test_private_media_cannot_be_attached_and_untrusted_copy_is_escaped(): void
    {
        $this->administrator();$media = Media::factory()->create(['visibility' => 'private']);
        $this->post(route('admin.clients.store'), ['name' => 'Client', 'slug' => 'client', 'category' => 'finance', 'image_id' => $media->id, 'status' => 'draft'])->assertSessionHasErrors('image_id');
        $logo = Media::factory()->create(['disk'=>'frontend','path'=>'images/logo-white.png','visibility'=>'public']);
        Client::create(['name' => '<script>alert(1)</script>', 'slug' => 'escaped', 'category' => 'finance', 'image_id'=>$logo->id, 'status' => 'published', 'published_at' => now()]);
        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_future_dated_or_draft_clients_are_not_shown(): void
    {
        Client::create(['name' => 'Future organization', 'slug' => 'future', 'category' => 'finance', 'status' => 'published', 'published_at' => now()->addDay()]);
        Client::create(['name' => 'Draft organization', 'slug' => 'draft', 'category' => 'finance', 'status' => 'draft']);
        $this->get('/about')->assertOk()->assertDontSee('Future organization')->assertDontSee('Draft organization');
    }

    public function test_forms_filters_and_direct_creation_use_only_draft_and_published(): void
    {
        $this->administrator();
        foreach (['admin.clients.index', 'admin.clients.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('value="draft"', false)->assertSee('value="published"', false)
                ->assertDontSee('value="review"', false)->assertDontSee('value="archived"', false);
        }
        $data = ['name' => 'Klien langsung tayang', 'slug' => 'klien-langsung-tayang', 'category' => 'finance'];
        foreach (['review', 'archived'] as $status) {
            $this->post(route('admin.clients.store'), $data + ['status' => $status])->assertSessionHasErrors('status');
        }
        $this->assertSame(0, Client::count());
        $this->post(route('admin.clients.store'), $data + ['status' => 'published'])->assertSessionHasNoErrors()->assertRedirect();
        $client = Client::sole();
        $this->assertNotNull($client->getPublishedRevision());
        $this->get('/clients')->assertOk()->assertSee($client->name);
        $this->get(route('admin.clients.edit', $client))->assertOk()->assertSee('Logo klien')->assertSee('name="image_id"', false)
            ->assertDontSee('Workflow editorial')->assertSee('value="published" selected', false);
        $this->get(route('admin.clients.index', ['status' => 'draft']))->assertOk()->assertDontSee($client->name);
    }

    public function test_editor_can_save_own_draft_but_cannot_publish_or_hide_live_clients(): void
    {
        $editor = User::factory()->active('editor')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $editor->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->actingAs($editor)->withSession(['auth.two_factor' => ['user_id' => $editor->id, 'auth_version' => $editor->auth_version, 'verified_at' => time()]]);
        $data = ['name' => 'Klien editor', 'slug' => 'klien-editor', 'category' => 'corporate'];
        $this->post(route('admin.clients.store'), $data + ['status' => 'published'])->assertForbidden();
        $this->assertSame(0, Client::count());
        $this->post(route('admin.clients.store'), $data + ['status' => 'draft'])->assertSessionHasNoErrors()->assertRedirect();
        $client = Client::sole();
        $this->assertSame($editor->id, $client->created_by);
        $client->update(['status' => 'published', 'published_at' => now()]);
        $this->put(route('admin.clients.update', $client), $data + ['status' => 'draft'])->assertForbidden();
        $this->assertSame('published', $client->fresh()->status->value);
    }

    public function test_legacy_status_migration_keeps_published_clients_and_history(): void
    {
        foreach (['review', 'archived', 'published'] as $status) {
            $client = Client::create(['name' => 'Klien '.$status, 'slug' => 'klien-'.$status, 'category' => 'finance', 'status' => $status]);
            $client->revisions()->create(['version' => 1, 'status' => $status, 'payload' => $client->attributesToArray()]);
        }
        $migration = require database_path('migrations/2026_11_02_000003_simplify_client_statuses.php');
        $migration->up();
        $this->assertSame(2, Client::where('status', 'draft')->count());
        $this->assertSame(1, Client::where('status', 'published')->count());
        foreach (Client::all() as $client) {
            $this->assertSame(1, $client->revisions()->count());
        }
    }
}
