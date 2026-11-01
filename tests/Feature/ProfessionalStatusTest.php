<?php

namespace Tests\Feature;

use App\Models\Industry;
use App\Models\Insight;
use App\Models\Media;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalStatusTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(string $role = 'admin'): User
    {
        $user = User::factory()->active($role)->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => [
            'user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time(),
        ]]);
        return $user;
    }

    private function payload(array $changes = []): array
    {
        return array_replace([
            'name' => 'Profesional Profil Uji', 'slug' => 'profesional-profil-uji',
            'position' => 'Partner', 'level' => 'partner', 'status' => 'draft',
        ], $changes);
    }

    public function test_forms_filters_and_validation_use_only_draft_and_published(): void
    {
        $this->signIn();
        $profile = Professional::factory()->create();
        foreach ([route('admin.professionals.index'), route('admin.professionals.create'), route('admin.professionals.edit', $profile)] as $url) {
            $this->get($url)->assertOk()->assertSee('value="draft"', false)->assertSee('value="published"', false)
                ->assertDontSee('value="review"', false)->assertDontSee('value="archived"', false)
                ->assertDontSee('Workflow editorial');
        }
        foreach (['review', 'archived'] as $status) {
            $this->post(route('admin.professionals.store'), $this->payload(['status' => $status]))->assertSessionHasErrors('status');
        }
        $this->assertSame(1, Professional::count());
    }

    public function test_publication_saves_photo_profile_fields_and_practice_relations_without_stale_revisions(): void
    {
        $this->signIn('super_admin');
        $practice = PracticeArea::factory()->create(['status' => 'published', 'published_at' => now()]);
        $unpublishedPractice = PracticeArea::factory()->create();
        $photo = Media::factory()->create();
        $this->post(route('admin.professionals.store'), $this->payload([
            'status' => 'published', 'photo_id' => $photo->id, 'biography' => 'Biografi awal',
            'practice_area_ids' => [$practice->id, $unpublishedPractice->id],
            'qualifications_text' => "Kualifikasi A\nKualifikasi B", 'languages_text' => "English\nIndonesia",
            'is_leadership' => 1, 'is_featured' => 1, 'sort_order' => 2,
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $profile = Professional::sole();
        $this->assertSame($photo->id, $profile->photo_id);
        $this->assertSame(2, $profile->practiceAreas()->count());
        $this->assertSame(['English', 'Indonesia'], $profile->languages);
        $this->assertNotNull($profile->getPublishedRevision());
        $this->get('/team')->assertOk()->assertSee($profile->name)
            ->assertViewHas('profileModalData', fn ($profiles) => $profiles[$profile->slug]['practiceAreas']->all() === [$practice->name]);
        $this->get(route('admin.professionals.edit', $profile))->assertOk()->assertViewHas('editable', true)
            ->assertSee('Foto profil')->assertSee('name="photo_id"', false)->assertSee('value="published" selected', false);

        $this->put(route('admin.professionals.update', $profile), $this->payload([
            'name' => 'Profesional Versi Terbaru', 'status' => 'published', 'biography' => 'Biografi baru',
            'qualifications_text' => 'Kualifikasi terbaru', 'languages_text' => 'English',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $profile->refresh();
        $this->assertSame(0, $profile->practiceAreas()->count());
        $this->assertSame(['Kualifikasi terbaru'], $profile->qualifications);
        $this->assertSame([], $profile->getPublishedRevision()->payload['practice_area_ids']);
        $this->get('/team')->assertOk()->assertSee('Profesional Versi Terbaru')->assertDontSee('Profesional Profil Uji')
            ->assertViewHas('profileModalData', fn ($profiles) => $profiles[$profile->slug]['biography'] === 'Biografi baru');
        $this->get(route('admin.professionals.edit', $profile))->assertOk()->assertSee('Profesional Versi Terbaru')->assertDontSee('Profesional Profil Uji');
        $this->assertSame(2, $profile->revisions()->count());
    }

    public function test_draft_hides_profile_from_team_industry_contacts_and_insight_author(): void
    {
        $this->signIn();
        $this->post(route('admin.professionals.store'), $this->payload(['status' => 'published']))->assertSessionHasNoErrors();
        $profile = Professional::sole();
        $industry = Industry::create(['name' => 'Industri uji', 'slug' => 'industri-uji', 'summary' => 'Ringkasan industri', 'status' => 'published', 'published_at' => now()]);
        $industry->keyContacts()->attach($profile);
        Insight::factory()->create(['author_id' => $profile->id, 'status' => 'published', 'published_at' => now()]);
        $urls = ['/team', route('industries.show', $industry->slug), '/insights'];
        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertSee($profile->name);
        }
        $this->put(route('admin.professionals.update', $profile), $this->payload(['status' => 'draft']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($profile->fresh()->published_at);
        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertDontSee($profile->name);
        }
        $this->assertSame(1, $industry->keyContacts()->count());
        $this->post(route('admin.professionals.publish', $profile))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/team')->assertOk()->assertSee($profile->name);
        $this->post(route('admin.professionals.archive', $profile))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('draft', $profile->fresh()->status->value);
    }

    public function test_editor_cannot_publish_hide_live_profiles_or_link_inaccessible_practices(): void
    {
        $editor = $this->signIn('editor');
        $this->post(route('admin.professionals.store'), $this->payload(['status' => 'published']))->assertForbidden();
        $privatePractice = PracticeArea::factory()->create(['created_by' => User::factory()->create()->id]);
        $this->post(route('admin.professionals.store'), $this->payload(['practice_area_ids' => [$privatePractice->id]]))->assertForbidden();
        $this->assertSame(0, Professional::count());
        $this->post(route('admin.professionals.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $profile = Professional::sole();
        $this->assertSame($editor->id, $profile->created_by);
        $profile->update(['status' => 'published', 'published_at' => now()]);
        $this->put(route('admin.professionals.update', $profile), $this->payload(['status' => 'draft']))->assertForbidden();
        $this->assertSame('published', $profile->fresh()->status->value);
    }

    public function test_legacy_status_migration_preserves_published_profiles_and_history(): void
    {
        foreach (['review', 'archived', 'published'] as $status) {
            $profile = Professional::factory()->create(['status' => $status]);
            $profile->revisions()->create(['version' => 1, 'status' => $status, 'payload' => $profile->attributesToArray()]);
        }
        $migration = require database_path('migrations/2026_11_02_000002_simplify_professional_statuses.php');
        $migration->up();
        $this->assertSame(2, Professional::where('status', 'draft')->count());
        $this->assertSame(1, Professional::where('status', 'published')->count());
        foreach (Professional::all() as $profile) {
            $this->assertSame(1, $profile->revisions()->count());
        }
    }
}
