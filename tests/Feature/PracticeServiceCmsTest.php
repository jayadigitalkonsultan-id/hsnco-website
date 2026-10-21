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

    public function test_admin_edits_are_isolated_until_publication_and_archive_hides_panel(): void
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
            'image_id' => $image, 'sort_order' => 0, 'status' => 'review',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/practice-areas')->assertOk()->assertDontSee('Legal tenaga kerja diperbarui');
        $this->post(route('admin.practice-areas.publish', $practice))->assertSessionHasNoErrors()->assertRedirect();
        $response = $this->get('/practice-areas')->assertOk()->assertSee('Legal tenaga kerja diperbarui')
            ->assertSee('Ringkasan panel dari CMS')->assertSee('Butir layanan pertama')->assertSee('Butir layanan kedua');
        $response->assertSeeInOrder(['Legal tenaga kerja diperbarui', 'Civil &amp; Commercial'], false);
        $this->assertSame($image, $practice->fresh()->image_id);
        $this->post(route('admin.practice-areas.archive', $practice))->assertRedirect();
        $this->get('/practice-areas')->assertOk()->assertDontSee('Legal tenaga kerja diperbarui');
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
}
