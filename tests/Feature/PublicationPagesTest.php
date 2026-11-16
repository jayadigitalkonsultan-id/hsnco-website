<?php
namespace Tests\Feature;

use App\Models\Insight;
use App\Models\News;
use App\Models\Media;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_combined_directory_and_home_link_to_both_article_types(): void
    {
        $insight = Insight::factory()->create(['title'=>'Legal perspective test','status'=>'published','published_at'=>now()->subDay()]);
        $news = News::create(['title'=>'Firm update test','slug'=>'firm-update-test','status'=>'published','published_at'=>now()]);
        Insight::factory()->create(['title'=>'HIDDEN DRAFT']);
        News::create(['title'=>'HIDDEN FUTURE','slug'=>'future-test','status'=>'published','published_at'=>now()->addDay()]);
        foreach (['insights','home'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Legal perspective test')->assertSee('Firm update test')
                ->assertSee(route('publications.show',['kind'=>'insight','slug'=>$insight->slug]),false)
                ->assertSee(route('publications.show',['kind'=>'news','slug'=>$news->slug]),false)
                ->assertDontSee('HIDDEN DRAFT')->assertDontSee('HIDDEN FUTURE');
        }
        $this->get('/insights')->assertRedirect('/news-insights');
        $this->get('/berita')->assertRedirect('/news-insights');
    }

    public function test_article_has_body_recommendations_and_safe_html_without_private_media(): void
    {
        $media=Media::factory()->create(['visibility'=>'private','path'=>'secret-photo.jpg']);
        $article=Insight::factory()->create(['title'=>'Current insight','slug'=>'same-slug','body'=>'<p>Approved article body</p><script>evil_script()</script>','status'=>'published','published_at'=>now(),'cover_media_id'=>$media->id]);
        $next=Insight::factory()->create(['title'=>'Recommended legal perspective','status'=>'published','published_at'=>now()->subDay()]);
        $this->get(route('publications.show',['kind'=>'insight','slug'=>$article->slug]))->assertOk()
            ->assertSee('Approved article body')->assertSee('Recommended Insights')->assertSee($next->title)
            ->assertDontSee('evil_script()')->assertDontSee('secret-photo.jpg');
        $recommended=app(\App\Services\Cms\PublicationFeed::class)->recommended($article);
        $this->assertFalse($recommended->contains('id',$article->id));
        $news=News::create(['title'=>'News with same slug','slug'=>'same-slug','body'=>'News body here','status'=>'published','published_at'=>now()]);
        $this->get(route('publications.show',['kind'=>'news','slug'=>$news->slug]))->assertOk()->assertSee('News body here');
    }

    public function test_unpublished_scheduled_deleted_and_unknown_articles_are_not_readable(): void
    {
        foreach(['draft','scheduled','deleted'] as $state){
            $item=Insight::factory()->create(['status'=>$state==='draft'?'draft':'published','published_at'=>$state==='scheduled'?now()->addDay():now()]);
            if($state==='deleted')$item->delete();
            $this->get(route('publications.show',['kind'=>'insight','slug'=>$item->slug]))->assertNotFound();
        }
        $this->get('/news-insights/other/missing')->assertNotFound();
    }

    public function test_union_pagination_retains_both_types_and_does_not_repeat_records(): void
    {
        Insight::factory()->count(6)->create(['status'=>'published','published_at'=>now()]);
        foreach(range(1,6) as $i)News::create(['title'=>'News '.$i,'slug'=>'news-'.$i,'status'=>'published','published_at'=>now()]);
        $page=app(\App\Services\Cms\PublicationFeed::class)->paginate();
        $this->assertSame(12,$page->total());$this->assertCount(9,$page->items());
        $this->get(route('insights',['page'=>2]))->assertOk();
    }

    public function test_admin_has_one_combined_directory_and_guest_cannot_access_it(): void
    {
        $this->get(route('admin.publications.index'))->assertRedirect();
        $user=User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','confirmed_at'=>now()]);
        Insight::factory()->create(['title'=>'Editable insight']);
        News::create(['title'=>'Editable news','slug'=>'editable-news','status'=>'draft']);
        $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]])
            ->get(route('admin.publications.index'))->assertOk()->assertSee('Editable insight')->assertSee('Editable news');
    }
}
