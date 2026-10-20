<?php
namespace Tests\Feature;

use App\Models\Professional;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalPortfolioImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_import_publishes_nineteen_profiles_with_confirmed_categories(): void
    {
        $this->artisan('hsnco:import-professionals',['--publish'=>true])->assertSuccessful();
        $this->assertSame(19,Professional::published()->count());
        foreach(['managing_partner'=>1,'partner'=>2,'associate'=>9,'trainee_associate'=>2,'paralegal'=>4,'professional'=>1] as $level=>$count) {
            $this->assertSame($count,Professional::where('level',$level)->count());
        }
        $this->assertSame(19,\App\Models\ContentRevision::where('revisionable_type',(new Professional)->getMorphClass())->count());
        $this->assertSame(0,Professional::whereNotNull('photo_id')->count());
        $this->get(route('team'))->assertOk()->assertSee('Managing Partner')->assertSee('Trainee Associate')->assertSee('Paralegal')->assertSee('dr. Hilwani')->assertSee('Foto akan diperbarui');
    }

    public function test_import_is_repeatable_and_preserves_cms_edits_and_deleted_records(): void
    {
        $this->artisan('hsnco:import-professionals',['--publish'=>true])->assertSuccessful();
        $profile=Professional::first();$profile->update(['biography'=>'Biografi hasil edit klien.','level'=>'professional']);
        $deleted=Professional::whereKeyNot($profile->id)->first();$deleted->delete();
        $this->artisan('hsnco:import-professionals',['--publish'=>true])->assertSuccessful();
        $this->assertSame(19,Professional::withTrashed()->count());
        $this->assertSame('Biografi hasil edit klien.',$profile->fresh()->biography);
        $this->assertSame('professional',$profile->fresh()->level);
        $this->assertTrue($deleted->fresh()->trashed());
    }

    public function test_dry_run_writes_nothing_and_default_import_keeps_profiles_in_draft(): void
    {
        $this->artisan('hsnco:import-professionals',['--dry-run'=>true,'--publish'=>true])->assertSuccessful();
        $this->assertSame(0,Professional::count());
        $this->artisan('hsnco:import-professionals')->assertSuccessful();
        $this->assertSame(19,Professional::count());$this->assertSame(0,Professional::published()->count());
        $this->get(route('team'))->assertOk()->assertDontSee('Assoc. Prof. Dr. Hasbullah');
    }

    public function test_explicit_category_sync_preserves_biography_and_updates_published_snapshot(): void
    {
        $this->artisan('hsnco:import-professionals',['--publish'=>true])->assertSuccessful();
        $profile=Professional::where('level','managing_partner')->first();
        $profile->update(['level'=>'partner','biography'=>'Konten dari klien tetap disimpan.']);
        $this->artisan('hsnco:import-professionals',['--publish'=>true,'--sync-categories'=>true])->assertSuccessful();
        $profile->refresh();
        $this->assertSame('managing_partner',$profile->level);
        $this->assertSame('Konten dari klien tetap disimpan.',$profile->biography);
        $this->assertSame('managing_partner',$profile->getPublishedRevision()->payload['level']);
        $this->assertSame(19,Professional::count());
    }
    public function test_backend_editor_offers_all_imported_categories_and_photo_field(): void
    {
        $this->artisan('hsnco:import-professionals',['--publish'=>true])->assertSuccessful();
        $user=User::factory()->active('super_admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','confirmed_at'=>now()]);
        $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]]);
        $response=$this->get(route('admin.professionals.edit',Professional::where('level','trainee_associate')->first()));
        $response->assertOk()->assertSee('Trainee Associate')->assertSee('Paralegal')->assertSee('Managing Partner')->assertSee('name="photo_id"',false);
    }
}