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

    public function test_admin_workflow_publishes_logo_details_and_archive_hides_client(): void
    {
        $this->administrator();
        $this->get(route('admin.clients.index'))->assertOk()->assertSee('Klien dan slideshow website');
        $this->get(route('admin.clients.create'))->assertOk()->assertSee('Sektor klien')->assertSee('Logo klien');
        $logo = Media::factory()->create(['disk' => 'frontend', 'path' => 'images/logo-white.png', 'visibility' => 'public']);
        $this->post(route('admin.clients.store'), [
            'name' => 'Klien uji editorial', 'slug' => 'klien-uji-editorial', 'category' => 'finance',
            'body' => 'Penugasan yang disetujui', 'source_note' => 'INTERNAL-NOT-PUBLIC',
            'image_id' => $logo->id, 'status' => 'review', 'sort_order' => 2,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $client = Client::where('slug', 'klien-uji-editorial')->firstOrFail();
        $this->get('/')->assertOk()->assertDontSee('Klien uji editorial');
        $this->post(route('admin.clients.publish', $client))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertOk()->assertSee('Klien uji editorial')->assertDontSee('Penugasan yang disetujui')
            ->assertSee('images/logo-white.png')->assertSee('assets/css/clients.css')->assertSee('assets/js/clients.js')->assertDontSee('INTERNAL-NOT-PUBLIC');
        $this->get('/about')->assertOk()->assertSee('Klien uji editorial')->assertSee('assets/css/clients.css')->assertSee('assets/js/clients.js');
        $this->put(route('admin.clients.update', $client), ['name' => 'Revisi nama klien', 'slug' => $client->slug, 'category' => 'corporate', 'status' => 'review'])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertDontSee('Revisi nama klien');
        $this->post(route('admin.clients.publish', $client))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertSee('Revisi nama klien');
        $this->post(route('admin.clients.archive', $client))->assertRedirect();
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
        $this->post(route('admin.clients.store'), ['name' => 'Client', 'slug' => 'client', 'category' => 'finance', 'image_id' => $media->id])->assertSessionHasErrors('image_id');
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
}