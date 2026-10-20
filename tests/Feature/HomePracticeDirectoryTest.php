<?php
namespace Tests\Feature;
use App\Models\PracticeArea;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePracticeDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function practices(): void
    {
        for($i=1;$i<=8;$i++) PracticeArea::factory()->create(['name'=>'Layanan terbit '.$i,'slug'=>'layanan-'.$i,'status'=>'published','published_at'=>now()->subMinute(),'sort_order'=>$i]);
        PracticeArea::factory()->create(['name'=>'Layanan draft rahasia','status'=>'draft']);
        PracticeArea::factory()->create(['name'=>'Layanan terjadwal','status'=>'published','published_at'=>now()->addDay()]);
    }

    public function test_fallback_home_shows_every_published_practice_with_valid_directory_links(): void
    {
        $this->practices();$response=$this->get('/')->assertOk()->assertSee('data-home-practices',false)->assertDontSee('Layanan draft rahasia')->assertDontSee('Layanan terjadwal');
        for($i=1;$i<=8;$i++)$response->assertSee('Layanan terbit '.$i)->assertSee(route('practice-areas').'#layanan-'.$i);
    }

    public function test_cms_home_shows_all_practices_instead_of_only_the_saved_selection(): void
    {
        $this->practices();
        $page=Page::factory()->create(['key'=>'home','slug'=>'home','template'=>'home','status'=>'published','published_at'=>now()->subMinute()]);
        $page->sections()->create(['key'=>'featured-practices','type'=>'practice_selection','heading'=>'Bidang praktik','is_visible'=>true,'sort_order'=>1,'content'=>['practice_area_ids'=>[PracticeArea::first()->id]]]);
        $response=$this->get('/')->assertOk()->assertDontSee('Layanan draft rahasia');
        for($i=1;$i<=8;$i++)$response->assertSee('Layanan terbit '.$i);
        $page->sections()->update(['is_visible'=>false]);
        $this->get('/')->assertOk()->assertDontSee('data-home-practices',false);
    }
}