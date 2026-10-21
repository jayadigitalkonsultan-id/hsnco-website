<?php

namespace Tests\Feature;

use App\Models\Industry;
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
        return ['name' => $industry->name, 'slug' => $industry->slug, 'summary' => 'Ringkasan hasil penyuntingan', 'body' => 'Industry overview dari CMS', 'focus_text' => "Transaksi\nKepatuhan", 'key_contact_ids' => $ids, 'sort_order' => 0, 'status' => 'review'];
    }

    public function test_admin_can_select_professionals_with_draft_isolation_and_portfolio_modal(): void
    {
        $industry = $this->import();
        $this->administrator();
        $person = Professional::factory()->create(['name' => 'Professional Contact Uji', 'status' => 'published', 'published_at' => now()->subMinute()]);
        $this->get(route('admin.industries.index'))->assertOk()->assertSee($industry->name);
        $this->get(route('admin.industries.edit', $industry))->assertOk()->assertSee('Key Contact')->assertSee($person->name);
        $this->put(route('admin.industries.update', $industry), $this->payload($industry, [$person->id]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(0, $industry->keyContacts()->count());
        $url = route('industries.show', $industry->slug);
        $this->get($url)->assertOk()->assertDontSee($person->name)->assertDontSee('Ringkasan hasil penyuntingan');
        $this->post(route('admin.industries.publish', $industry))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(1, $industry->keyContacts()->count());
        $this->get($url)->assertOk()->assertSee($person->name)->assertSee('Ringkasan hasil penyuntingan')->assertSee('data-profile="'.$person->slug.'"', false)->assertSee('id="detailModal"', false);
        $this->put(route('admin.industries.update', $industry), $this->payload($industry, []))->assertSessionHasNoErrors();
        $this->get($url)->assertSee($person->name);
        $this->post(route('admin.industries.publish', $industry))->assertSessionHasNoErrors();
        $this->get($url)->assertDontSee($person->name);
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
}
