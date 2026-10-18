<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\Cms\PracticeAreaRequest;
use App\Models\AuditLog;
use App\Models\ContentRevision;
use App\Models\Enquiry;
use App\Models\EnquiryNote;
use App\Models\Insight;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PracticeArea;
use App\Models\Professional;
use App\Models\SiteSetting;
use App\Models\TwoFactorChallenge;
use App\Models\TwoFactorRecoveryCode;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\UserTwoFactorAuthentication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_factories_generate_ulids_and_relations_round_trip(): void
    {
        foreach ([Page::class, PageSection::class, PracticeArea::class, Professional::class, Insight::class, Media::class, MediaUsage::class, ContentRevision::class, SiteSetting::class, AuditLog::class, Enquiry::class, EnquiryNote::class, UserInvitation::class, UserTwoFactorAuthentication::class, TwoFactorRecoveryCode::class, TwoFactorChallenge::class] as $class) {
            $model = $class::factory()->create();
            $this->assertTrue(Str::isUlid($model->id), $class);
            $this->assertFalse($model->getIncrementing());
            $this->assertSame($model->id, $model->fresh()->id);
        }

        $professional = Professional::factory()->create();
        $practice = PracticeArea::factory()->create();
        $professional->practiceAreas()->attach($practice);
        $this->assertSame($practice->id, $professional->practiceAreas->sole()->id);
        $this->assertSame($professional->id, $practice->professionals->sole()->id);
    }

    public function test_invited_users_have_no_credentials_and_security_state_is_guarded(): void
    {
        $user = User::create(['email' => '  EDITOR@EXAMPLE.TEST ', 'role' => 'super_admin', 'status' => 'active']);
        $user->refresh();
        $this->assertTrue(Str::isUlid($user->id));
        $this->assertSame('editor@example.test', $user->email);
        $this->assertNull($user->password);
        $this->assertNull($user->name);
        $this->assertSame(UserStatus::Invited, $user->status);
        $this->assertSame(UserRole::Editor, $user->role);
        $this->assertFalse($user->isActive());
    }

    public function test_security_rollback_refuses_to_discard_invited_accounts(): void
    {
        $user = User::factory()->invited()->create();
        $migration = require database_path('migrations/2026_10_16_000001_add_security_fields_to_users_table.php');
        try {
            $migration->down();
            $this->fail('Nullable credentials cannot be discarded by rollback.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('pending invitations', $exception->getMessage());
        }
        $this->assertNull($user->fresh()->password);
        $this->assertTrue(Schema::hasColumn('users', 'status'));
    }

    public function test_security_secrets_are_encrypted_or_hidden_from_serialization(): void
    {
        $factor = UserTwoFactorAuthentication::factory()->create(['secret' => 'secret-never-in-json']);
        $this->assertNotSame('secret-never-in-json', DB::table($factor->getTable())->where('id', $factor->id)->value('secret'));
        $this->assertSame('secret-never-in-json', $factor->fresh()->secret);
        $this->assertArrayNotHasKey('secret', $factor->toArray());
        $this->assertArrayNotHasKey('token_hash', UserInvitation::factory()->create()->toArray());
        $this->assertArrayNotHasKey('code_hash', TwoFactorRecoveryCode::factory()->create()->toArray());
        $this->assertArrayNotHasKey('session_binding_hash', TwoFactorChallenge::factory()->create()->toArray());
    }

    public function test_seed_is_idempotent_and_does_not_publish_or_overwrite_content(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(7, Page::count());
        $this->assertSame(9, PracticeArea::count());
        $this->assertSame(6, Professional::count());
        $this->assertSame(3, Insight::count());
        $this->assertSame(25, ContentRevision::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Enquiry::count());
        $this->assertSame(0, Page::published()->count());
        $page = Page::where('key', 'home')->firstOrFail();
        $page->update(['title' => 'Edited by owner']);
        $section = $page->sections->first();
        $section->update(['heading' => 'Edited heading']);
        $this->seed(DatabaseSeeder::class);
        $this->assertSame('Edited by owner', $page->fresh()->title);
        $this->assertSame('Edited heading', $section->fresh()->heading);
        $this->assertSame(7, Page::count());
        $this->assertSame(25, ContentRevision::count());
        $this->assertCount(11, $page->sections);
        $selected = $page->sections()->where('key', 'featured-practices')->firstOrFail()->content['practice_area_ids'];
        $this->assertCount(4, $selected);
        $this->assertSame(4, PracticeArea::whereIn('id', $selected)->count());
        $navigation = SiteSetting::where('key', 'navigation.main')->firstOrFail()->value['items'];
        $this->assertCount(6, $navigation);
        $this->assertSame('Beranda', $navigation[0]['label']);
    }

    public function test_drafts_and_scheduled_articles_are_excluded_from_public_scope(): void
    {
        Insight::factory()->create(['status' => 'draft']);
        Insight::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);
        Insight::factory()->create(['status' => 'published', 'published_at' => null]);
        $published = Insight::factory()->create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $this->assertSame($published->id, Insight::published()->sole()->id);
        $revision = $published->revisions()->create(['version' => 1, 'status' => 'draft', 'payload' => ['title' => 'Unapproved edit']]);
        $this->assertNotSame($revision->payload['title'], $published->fresh()->title);
        $published->delete();
        $this->assertSame(0, Insight::published()->count());
    }

    public function test_revision_versions_must_be_unique_per_target(): void
    {
        $page = Page::factory()->create();
        $page->revisions()->create(['version' => 1, 'status' => 'draft', 'payload' => []]);
        $this->expectException(QueryException::class);
        $page->revisions()->create(['version' => 1, 'status' => 'draft', 'payload' => []]);
    }

    public function test_submission_keys_are_unique(): void
    {
        $enquiry = Enquiry::factory()->create();
        $this->expectException(QueryException::class);
        Enquiry::factory()->create(['submission_key' => $enquiry->submission_key]);
    }

    public function test_foreign_keys_reject_missing_targets(): void
    {
        $this->expectException(QueryException::class);
        PageSection::factory()->create(['page_id' => strtolower((string) Str::ulid())]);
    }

    public function test_archived_slugs_remain_reserved(): void
    {
        $practice = PracticeArea::factory()->create();
        $practice->delete();
        $this->expectException(QueryException::class);
        PracticeArea::factory()->create(['slug' => $practice->slug]);
    }

    public function test_pivot_and_notes_cascade_only_when_the_parent_is_hard_deleted(): void
    {
        $practice = PracticeArea::factory()->create();
        $professional = Professional::factory()->create();
        $practice->professionals()->attach($professional);
        $practice->delete();
        $this->assertSame(1, DB::table('practice_area_professional')->count());
        $practice->forceDelete();
        $this->assertSame(0, DB::table('practice_area_professional')->count());
        $this->assertNotNull($professional->fresh());

        $enquiry = Enquiry::factory()->create();
        $note = EnquiryNote::factory()->create(['enquiry_id' => $enquiry->id]);
        $enquiry->delete();
        $this->assertNotNull($note->fresh());
        $enquiry->forceDelete();
        $this->assertNull($note->fresh());
    }

    public function test_deleting_users_preserves_audit_and_cascades_authentication_children(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create(['user_id' => $user->id]);
        $invitation = UserInvitation::factory()->create(['user_id' => $user->id]);
        $factor = UserTwoFactorAuthentication::factory()->create(['user_id' => $user->id]);
        $user->delete();
        $this->assertNull($log->fresh()->user_id);
        $this->assertNull($invitation->fresh());
        $this->assertNull($factor->fresh());
    }

    public function test_media_cannot_be_deleted_while_referenced_including_soft_deleted_content(): void
    {
        $media = Media::factory()->create();
        $page = Page::factory()->create(['og_media_id' => $media->id]);
        $page->delete();
        $this->expectException(\LogicException::class);
        $media->delete();
    }

    public function test_database_restricts_hard_deletion_of_referenced_media(): void
    {
        $media = Media::factory()->create();
        Page::factory()->create(['og_media_id' => $media->id]);
        $this->expectException(QueryException::class);
        DB::table('media')->where('id', $media->id)->delete();
    }

    public function test_editor_permissions_do_not_grant_publishing_or_invitation_access(): void
    {
        $editor = User::factory()->active()->create();
        $other = User::factory()->active()->create();
        $admin = User::factory()->active('admin')->create();
        $super = User::factory()->active('super_admin')->create();
        $page = Page::factory()->create(['created_by' => $editor->id]);
        $this->assertTrue(Gate::forUser($editor)->allows('update', $page));
        $this->assertFalse(Gate::forUser($other)->allows('update', $page));
        $this->assertFalse(Gate::forUser($editor)->allows('publish', $page));
        $this->assertTrue(Gate::forUser($admin)->allows('publish', $page));
        $this->assertFalse(Gate::forUser($admin)->allows('create', User::class));
        $this->assertTrue(Gate::forUser($super)->allows('create', User::class));
        $page->update(['status' => 'published']);
        $this->assertFalse(Gate::forUser($admin)->allows('update', $page));
        $this->assertTrue(Gate::forUser($admin)->allows('revise', $page));
        $editor->forceFill(['status' => 'inactive'])->save();
        $this->assertFalse(Gate::forUser($editor)->allows('create', Page::class));
    }

    public function test_content_requests_reject_publication_and_actor_injection(): void
    {
        $request = new PracticeAreaRequest;
        $rules = $request->rules();
        $validator = Validator::make(['name' => 'Practice', 'slug' => 'valid-slug', 'status' => 'published', 'created_by' => (string) Str::ulid()], $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('status'));
        $this->assertTrue($validator->errors()->has('created_by'));
        $this->assertFalse(Validator::make(['name' => 'Practice', 'slug' => 'valid-slug', 'status' => 'draft'], $rules)->fails());
    }

    public function test_audit_logs_are_append_only(): void
    {
        $log = AuditLog::factory()->create();
        $this->expectException(\LogicException::class);
        $log->update(['event' => 'rewritten']);
    }

    public function test_published_revision_snapshots_are_immutable(): void
    {
        $revision = ContentRevision::factory()->create(['status' => ContentStatus::Published]);
        $this->expectException(\LogicException::class);
        $revision->update(['payload' => ['title' => 'rewritten']]);
    }
}
