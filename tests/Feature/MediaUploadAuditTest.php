<?php
namespace Tests\Feature;
use App\Models\Media;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class MediaUploadAuditTest extends TestCase
{
    use RefreshDatabase;
    public function test_upload_update_and_archive_write_media_audit_events(): void
    {
        Storage::fake('public');
        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id, 'method'=>'email', 'secret'=>null, 'confirmed_at'=>now()]);
        $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]]);
        $this->post(route('admin.media.store'), ['file'=>UploadedFile::fake()->image('portrait.jpg',320,240),'visibility'=>'public','alt_text'=>'Portrait'])->assertSessionHasNoErrors()->assertRedirect();
        $media=Media::sole();
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame('media',$media->getMorphClass());
        $this->assertTrue(Relation::requiresMorphMap());
        $this->assertSame(Media::class,Relation::getMorphedModel('media'));
        $this->assertDatabaseHas('audit_logs',['event'=>'media_uploaded','subject_type'=>'media','subject_id'=>$media->id,'outcome'=>'success']);
        $this->put(route('admin.media.update',$media),['alt_text'=>'Updated portrait'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('audit_logs',['event'=>'media_updated','subject_type'=>'media','subject_id'=>$media->id]);
        $this->delete(route('admin.media.destroy',$media))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSoftDeleted($media);
        $this->assertDatabaseHas('audit_logs',['event'=>'media_deleted','subject_type'=>'media','subject_id'=>$media->id]);
        Storage::disk('public')->assertExists($media->path);
    }
}
