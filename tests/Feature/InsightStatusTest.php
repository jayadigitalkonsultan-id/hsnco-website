<?php

namespace Tests\Feature;

use App\Models\Insight;
use App\Models\Page;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsightStatusTest extends TestCase
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

    public function test_forms_and_filters_have_two_statuses_and_reject_legacy_values(): void
    {
        $this->signIn();
        $insight = Insight::factory()->create(['status' => 'draft']);
        foreach ([route('admin.insights.index'), route('admin.insights.create'), route('admin.insights.edit', $insight)] as $url) {
            $this->get($url)->assertOk()->assertSee('value="draft"', false)->assertSee('value="published"', false)
                ->assertDontSee('value="review"', false)->assertDontSee('value="archived"', false)
                ->assertDontSee('Workflow editorial');
        }
        foreach (['review', 'archived'] as $status) {
            $this->put(route('admin.insights.update', $insight), [
                'title' => $insight->title, 'slug' => $insight->slug, 'status' => $status,
            ])->assertSessionHasErrors('status');
        }
        $this->assertSame('draft', $insight->fresh()->status->value);
    }

    public function test_publishing_and_returning_to_draft_update_public_pages_and_preserve_revisions(): void
    {
        $this->signIn('super_admin');
        $home = Page::factory()->create([
            'key' => 'home', 'slug' => 'home', 'template' => 'home', 'status' => 'published', 'published_at' => now(),
        ]);
        $home->sections()->create([
            'key' => 'latest-insights', 'type' => 'latest_insights', 'heading' => 'Latest insights',
            'is_visible' => true, 'sort_order' => 1, 'content' => ['limit' => 3],
        ]);
        $this->post(route('admin.insights.store'), [
            'title' => 'Insight langsung tayang', 'slug' => 'insight-langsung-tayang',
            'excerpt' => 'Ringkasan publikasi', 'body' => 'Isi publikasi terbaru', 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $insight = Insight::sole();
        $this->assertNotNull($insight->getPublishedRevision());
        foreach (['/insights', '/'] as $url) {
            $this->get($url)->assertOk()->assertSee('Insight langsung tayang');
        }
        $this->put(route('admin.insights.update', $insight), [
            'title' => 'Insight revisi terkini', 'slug' => $insight->slug, 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/insights')->assertOk()->assertSee('Insight revisi terkini')->assertDontSee('Insight langsung tayang');
        $this->get(route('admin.insights.edit', $insight))->assertOk()->assertSee('Insight revisi terkini')
            ->assertSee('value="published" selected', false)->assertDontSee('Insight langsung tayang');
        $this->put(route('admin.insights.update', $insight), [
            'title' => 'Insight draf tersembunyi', 'slug' => $insight->slug, 'status' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();
        foreach (['/insights', '/'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Insight draf tersembunyi')->assertDontSee('Insight revisi terkini');
        }
        $this->assertNull($insight->fresh()->published_at);
        $this->assertSame(3, $insight->revisions()->count());
        $this->post(route('admin.insights.publish', $insight))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/insights')->assertOk()->assertSee('Insight draf tersembunyi');
        $this->post(route('admin.insights.archive', $insight))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('draft', $insight->fresh()->status->value);
    }

    public function test_editor_can_save_own_draft_but_cannot_publish_or_hide_live_insight(): void
    {
        $editor = $this->signIn('editor');
        $data = ['title' => 'Insight editor', 'slug' => 'insight-editor', 'status' => 'published'];
        $this->post(route('admin.insights.store'), $data)->assertForbidden();
        $this->assertSame(0, Insight::count());
        $this->post(route('admin.insights.store'), array_replace($data, ['status' => 'draft']))
            ->assertSessionHasNoErrors()->assertRedirect();
        $insight = Insight::sole();
        $this->assertSame($editor->id, $insight->created_by);
        $insight->update(['status' => 'published', 'published_at' => now()]);
        $this->put(route('admin.insights.update', $insight), array_replace($data, ['status' => 'draft']))->assertForbidden();
        $this->assertSame('published', $insight->fresh()->status->value);
    }

    public function test_migration_maps_legacy_states_to_draft_and_keeps_history(): void
    {
        foreach (['review', 'archived', 'published'] as $status) {
            $insight = Insight::factory()->create(['status' => $status]);
            $insight->revisions()->create(['version' => 1, 'status' => $status, 'payload' => $insight->attributesToArray()]);
        }
        $migration = require database_path('migrations/2026_11_02_000001_simplify_insight_statuses.php');
        $migration->up();
        $this->assertSame(2, Insight::where('status', 'draft')->count());
        $this->assertSame(1, Insight::where('status', 'published')->count());
        foreach (Insight::all() as $insight) {
            $this->assertSame(1, $insight->revisions()->count());
        }
    }
}
