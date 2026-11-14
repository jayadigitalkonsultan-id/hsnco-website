<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_three_latest_published_news_below_insights_in_both_page_modes(): void
    {
        foreach (range(1, 4) as $number) {
            News::create([
                'title' => "Published news {$number}",
                'slug' => "published-news-{$number}",
                'status' => 'published',
                'published_at' => now()->subDays(5 - $number),
            ]);
        }
        News::create([
            'title' => 'Private draft news', 'slug' => 'private-draft-news',
            'status' => 'draft', 'published_at' => null,
        ]);
        News::create([
            'title' => 'Scheduled news', 'slug' => 'scheduled-news',
            'status' => 'published', 'published_at' => now()->addDay(),
        ]);

        $this->assertHomeNews();

        Page::factory()->create([
            'key' => 'home', 'slug' => 'home', 'template' => 'home',
            'status' => 'published', 'published_at' => now(),
        ])->sections()->create([
            'key' => 'latest-insights', 'type' => 'latest_insights',
            'heading' => 'Latest insights', 'is_visible' => true,
            'sort_order' => 1, 'content' => ['limit' => 3],
        ]);

        $this->assertHomeNews();
    }

    private function assertHomeNews(): void
    {
        $response = $this->get(route('home'))->assertOk()
            ->assertSeeInOrder(['Latest insights', 'id="recent-news"', 'Recent news.'], false)
            ->assertSeeInOrder(['Published news 4', 'Published news 3', 'Published news 2'])
            ->assertSee('href="'.route('news').'#published-news-4"', false)
            ->assertDontSee('Published news 1')
            ->assertDontSee('Private draft news')
            ->assertDontSee('Scheduled news');

        $this->assertSame(1, substr_count($response->getContent(), 'id="recent-news"'));
    }
}
