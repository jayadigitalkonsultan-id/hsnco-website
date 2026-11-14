<?php

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalPhotoFramingTest extends TestCase
{
    use RefreshDatabase;

    private function loginAdmin(): void
    {
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => [
            'user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time(),
        ]]);
    }

    public function test_admin_can_preview_save_and_reopen_individual_framing(): void
    {
        $this->loginAdmin();
        $person = Professional::factory()->create();
        $this->get(route('admin.professionals.edit', $person))->assertOk()->assertSee('data-portrait-editor', false);
        $framing = ['zoom' => 1.45, 'x' => 35, 'y' => 70];
        $this->put(route('admin.professionals.update', $person), [
            'name' => $person->name, 'slug' => $person->slug, 'position' => $person->position,
            'level' => $person->level, 'status' => 'draft', 'photo_framing' => $framing,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertEquals($framing, $person->fresh()->photo_framing);
        $this->get(route('admin.professionals.edit', $person))->assertOk()->assertSee('value="1.45"', false);
    }

    public function test_invalid_framing_is_rejected_without_updating_the_record(): void
    {
        $this->loginAdmin();
        $person = Professional::factory()->create();
        $this->put(route('admin.professionals.update', $person), [
            'name' => $person->name, 'slug' => $person->slug, 'position' => $person->position,
            'level' => $person->level, 'status' => 'draft', 'photo_framing' => ['zoom' => 99, 'x' => -1, 'y' => 101],
        ])->assertSessionHasErrors(['photo_framing.zoom', 'photo_framing.x', 'photo_framing.y']);
        $this->assertNull($person->fresh()->photo_framing);
    }

    public function test_public_card_uses_saved_framing_instead_of_global_zoom(): void
    {
        $person = Professional::factory()->create(['photo_framing' => ['zoom' => 1.1, 'x' => 25, 'y' => 65]]);
        $person->public_photo_url = '/storage/media/portrait.jpg';
        $person->setRelation('publicPhoto', null);
        $person->setRelation('publishedPracticeAreas', collect());
        $html = \Illuminate\Support\Facades\Blade::render('<x-frontend.professional-card :professional="$person" />', ['person' => $person]);
        $this->assertStringContainsString('--portrait-zoom: 1.1;', $html);
        $this->assertStringContainsString('object-position: 25% 65%;', $html);
        $this->assertStringContainsString('--portrait-origin: 25% 65%', $html);
    }
}
