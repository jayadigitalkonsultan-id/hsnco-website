<?php

namespace Tests\Feature;

use App\Models\ContentRevision;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Media;
use App\Models\Page;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    private function localPreview(): void
    {
        $this->app['env'] = 'local';
        config(['app.env' => 'local', 'admin.local_preview' => true]);
    }

    private function verifiedUser(string $role = 'admin'): User
    {
        $user = User::factory()->active($role)->create();
        UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id, 'method' => 'email', 'secret' => null, 'confirmed_at' => now()]);
        $this->actingAs($user)->withSession(['auth.two_factor' => ['user_id' => $user->id, 'auth_version' => $user->auth_version, 'verified_at' => time()]]);

        return $user;
    }

    public function test_local_preview_renders_all_eight_modules_with_server_data_and_scoped_plugins(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->localPreview();
        foreach (['', '/pages', '/practice-areas', '/professionals', '/insights', '/media', '/enquiries', '/settings'] as $path) {
            $response = $this->get('http://127.0.0.1/admin'.$path)->assertOk()->assertSee('Pratinjau lokal')->assertSee('id="adminSidebar"', false)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, 'bootstrap.min.css'));
            $this->assertSame(1, substr_count($html, 'bootstrap.bundle.min.js'));
            $this->assertStringNotContainsString('store.js', $html);
            $this->assertStringNotContainsString('localStorage', $html);
            $this->assertStringNotContainsString('data-export-demo', $html);
            $this->assertSame($path === '', str_contains($html, 'chart.umd.min.js'));
            $this->assertSame($path === '/pages', str_contains($html, '/jstree/jstree.min.js'));
            $this->assertSame($path === '/pages', str_contains($html, '/jquery/jquery.min.js'));
        }
        $this->get('/admin/pages')->assertSee(Page::where('key', 'about')->firstOrFail()->title);
        $this->get('/admin/practice-areas/create')->assertSee('fieldset disabled', false);
        $this->get('/admin/insights/create')->assertSee('fieldset disabled', false);
    }

    public function test_preview_is_not_a_write_or_private_data_bypass(): void
    {
        $this->localPreview();
        $enquiry = Enquiry::factory()->create(['message' => 'PRIVATE-ENQUIRY-MESSAGE']);
        $media = Media::factory()->create(['visibility' => 'private']);
        $this->post('/admin/insights', ['title' => 'Unauthorized'])->assertStatus(419);
        $this->withSession(['_token' => 'preview-csrf-token']);
        $this->post('/admin/insights', ['title' => 'Unauthorized', '_token' => 'preview-csrf-token'])->assertRedirect(route('login'));
        $this->put('/admin/settings', ['name' => 'Unauthorized', '_token' => 'preview-csrf-token'])->assertRedirect(route('login'));
        $this->get('/admin/enquiries')->assertOk()->assertDontSee('PRIVATE-ENQUIRY-MESSAGE')->assertDontSee($enquiry->email);
        $this->get(route('admin.enquiries.show', $enquiry))->assertForbidden();
        $this->get(route('admin.media.edit', $media))->assertForbidden();
        $this->get(route('admin.media.file', $media))->assertForbidden();
        $this->assertSame(0, Insight::count());
    }

    public function test_preview_cannot_be_enabled_on_production_or_spoofed_from_a_remote_peer(): void
    {
        $this->app['env'] = 'production';
        config(['admin.local_preview' => true]);
        $this->get('/admin')->assertRedirect(route('login'));
        $this->localPreview();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.8'])->withHeaders(['X-Forwarded-For' => '127.0.0.1'])->get('/admin')->assertRedirect(route('login'));
    }

    public function test_password_only_stale_two_factor_and_inactive_accounts_are_denied(): void
    {
        $user = User::factory()->active()->create();
        $this->actingAs($user)->get('/admin')->assertRedirect(route('two-factor.challenge'));
        $user = $this->verifiedUser();
        $this->get('/admin')->assertOk();
        $user->forceFill(['auth_version' => 1])->save();
        $this->get('/admin')->assertRedirect(route('two-factor.challenge'));
        $this->withSession(['auth.two_factor.auth_version' => 1]);
        $user->forceFill(['status' => 'inactive'])->save();
        $this->get('/admin')->assertRedirect(route('two-factor.challenge'));
    }

    public function test_content_modules_create_update_snapshot_and_soft_delete_using_policies(): void
    {
        $user = $this->verifiedUser();
        $cases = [
            'pages' => [Page::class, ['title' => 'Halaman Baru', 'slug' => 'halaman-baru', 'key' => 'home', 'template' => 'home', 'status' => 'draft']],
            'practice-areas' => [PracticeArea::class, ['name' => 'Praktik Baru', 'slug' => 'praktik-baru', 'status' => 'draft']],
            'professionals' => [Professional::class, ['name' => 'Profesional Baru', 'slug' => 'profesional-baru', 'position' => 'Mitra', 'level' => 'partner', 'status' => 'draft', 'qualifications_text' => "Kualifikasi A\nKualifikasi B", 'languages_text' => 'Indonesia']],
            'insights' => [Insight::class, ['title' => 'Publikasi Baru', 'slug' => 'publikasi-baru', 'status' => 'draft']],
        ];
        foreach ($cases as $module => [$model, $data]) {
            $this->post(route('admin.'.$module.'.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
            $record = $model::sole();
            $this->assertSame($user->id, $record->created_by);
            $this->assertSame(1, $record->revisions()->count());
            $this->get(route('admin.'.$module.'.edit', $record))->assertOk();
            $data[config('admin.modules.'.$module.'.title_field')] = 'Diubah oleh editor';
            $this->put(route('admin.'.$module.'.update', $record), $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame(2, $record->revisions()->count());
            $this->assertSame('Diubah oleh editor', $record->fresh()->{config('admin.modules.'.$module.'.title_field')});
            $this->delete(route('admin.'.$module.'.destroy', $record))->assertRedirect();
            $this->assertSoftDeleted($record);
        }
        $this->assertSame(8, ContentRevision::count());
    }

    public function test_editor_lists_and_updates_are_scoped_and_cannot_publish_or_inject_actor_ids(): void
    {
        $editor = $this->verifiedUser('editor');
        $mine = Insight::factory()->create(['title' => 'OWN-CONTENT', 'created_by' => $editor->id]);
        $other = Insight::factory()->create(['title' => 'OTHER-PRIVATE-CONTENT', 'created_by' => User::factory()->create()->id]);
        $this->get('/admin/insights')->assertOk()->assertSee('OWN-CONTENT')->assertDontSee('OTHER-PRIVATE-CONTENT');
        $this->put(route('admin.insights.update', $other), ['title' => 'Tampered', 'slug' => $other->slug])->assertForbidden();
        $this->put(route('admin.insights.update', $mine), ['title' => 'Tampered', 'slug' => $mine->slug, 'status' => 'published', 'created_by' => $other->created_by])->assertSessionHasErrors(['status', 'created_by']);
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/enquiries')->assertForbidden();
    }

    public function test_published_records_cannot_be_updated_directly_and_filtering_is_server_side(): void
    {
        $user = $this->verifiedUser();
        $published = Insight::factory()->create(['title' => 'PUBLISHED-ARTICLE', 'status' => 'published', 'published_at' => now()->subDay(), 'created_by' => $user->id]);
        Insight::factory()->count(12)->create(['title' => 'DRAFT-ARTICLE', 'created_by' => $user->id]);
        $this->get('/admin/insights?status=published&q=PUBLISHED&per_page=10')->assertOk()->assertSee('PUBLISHED-ARTICLE')->assertDontSee('DRAFT-ARTICLE');
        $this->get('/admin/insights?status=draft&per_page=10&page=2')->assertOk()->assertSee('11–12 dari 12');
        $this->put(route('admin.insights.update', $published), ['title' => 'Overwritten', 'slug' => $published->slug, 'status' => 'draft'])->assertForbidden();
        $this->assertSame('PUBLISHED-ARTICLE', $published->fresh()->title);
    }

    public function test_upload_uses_validated_files_and_metadata_updates_cannot_replace_the_file(): void
    {
        Storage::fake('public');
        $this->verifiedUser();
        $this->post(route('admin.media.store'), ['file' => UploadedFile::fake()->image('approved.jpg', 320, 240), 'visibility' => 'public', 'alt_text' => 'Approved image'])->assertSessionHasNoErrors()->assertRedirect();
        $media = Media::sole();
        Storage::disk('public')->assertExists($media->path);
        $this->get(route('admin.media.file', $media))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->put(route('admin.media.update', $media), ['file' => UploadedFile::fake()->image('replacement.jpg'), 'visibility' => 'private'])->assertSessionHasErrors(['file', 'visibility']);
        $this->put(route('admin.media.update', $media), ['alt_text' => 'Updated alternative text'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Updated alternative text', $media->fresh()->alt_text);
        $this->post(route('admin.media.store'), ['file' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')])->assertSessionHasErrors('file');
    }

    public function test_media_references_block_deletion_and_private_images_are_not_visible_to_other_editors(): void
    {
        $user = $this->verifiedUser('editor');
        $media = Media::factory()->create(['uploaded_by' => $user->id]);
        Page::factory()->create(['og_media_id' => $media->id]);
        $this->delete(route('admin.media.destroy', $media))->assertSessionHasErrors('media');
        $this->assertNotSoftDeleted($media);
        $private = Media::factory()->create(['visibility' => 'private', 'uploaded_by' => User::factory()->create()->id]);
        $this->get(route('admin.media.file', $private))->assertForbidden();
    }

    public function test_enquiry_status_notes_and_settings_use_the_database_and_escape_output(): void
    {
        $user = $this->verifiedUser();
        $enquiry = Enquiry::factory()->create(['message' => '<script>privateMessage()</script>']);
        $this->get(route('admin.enquiries.show', $enquiry))->assertOk()->assertSee('&lt;script&gt;privateMessage()', false)->assertDontSee('<script>privateMessage()', false);
        $this->put(route('admin.enquiries.update', $enquiry), ['status' => 'in_progress', 'assigned_to' => $user->id])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull($enquiry->fresh()->read_at);
        $this->post(route('admin.enquiries.notes.store', $enquiry), ['body' => 'Internal follow up'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($user->id, $enquiry->notes()->sole()->user_id);
        $this->put(route('admin.settings.update'), ['name' => 'HSNCO', 'tagline' => 'Informasi firma', 'contact_verified' => '0', 'email' => 'office@example.test'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('office@example.test', SiteSetting::where('key', 'contact.details')->sole()->value['email']);
    }
}
