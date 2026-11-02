<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_published_clients_are_shown_including_clients_without_logos(): void
    {
        $this->artisan('hsnco:import-clients', ['--publish' => true])->assertSuccessful();
        $response = $this->get(route('clients'))->assertOk();
        foreach (Client::all() as $client) {
            $response->assertSee($client->name);
        }
        $this->assertSame(43, substr_count($response->getContent(), 'data-client-entry'));
        $response->assertDontSee('data-client-filter', false)->assertDontSee('client-card-copy', false);
    }

    public function test_carousel_heading_links_to_directory_and_public_logo_is_displayed(): void
    {
        $logo = Media::factory()->create(['disk' => 'frontend', 'path' => 'images/logo-white.png', 'visibility' => 'public']);
        Client::create(['name' => 'Klien Logo Publik', 'slug' => 'logo-publik', 'category' => 'finance', 'image_id' => $logo->id, 'status' => 'published', 'published_at' => now()->subMinute()]);
        foreach (['/', '/about'] as $path) {
            $this->get($path)->assertOk()->assertSee('href="'.route('clients').'" aria-label="View all our clients"', false);
        }
        $this->get(route('clients'))->assertOk()->assertSee('alt="Klien Logo Publik"', false)->assertSee(asset('assets/images/logo-white.png'));
    }

    public function test_unpublished_future_deleted_and_private_media_are_not_exposed(): void
    {
        foreach (['draft', 'archived'] as $status) {
            Client::create(['name' => 'Invisible '.$status, 'slug' => $status, 'category' => 'finance', 'status' => $status, 'published_at' => now()]);
        }
        Client::create(['name' => 'Invisible future', 'slug' => 'future', 'category' => 'finance', 'status' => 'published', 'published_at' => now()->addDay()]);
        $deleted = Client::create(['name' => 'Invisible deleted', 'slug' => 'deleted', 'category' => 'finance', 'status' => 'published', 'published_at' => now()]);
        $deleted->delete();
        $private = Media::factory()->create(['visibility' => 'private', 'path' => 'private-logo-secret.png']);
        Client::create(['name' => 'Nama tanpa logo publik', 'slug' => 'private-logo', 'category' => 'finance', 'image_id' => $private->id, 'status' => 'published', 'published_at' => now()]);
        $this->get(route('clients'))->assertOk()->assertDontSee('Invisible')->assertDontSee('private-logo-secret.png')->assertSee('Nama tanpa logo publik');
    }

    public function test_empty_directory_and_sitemap_work(): void
    {
        $this->get(route('clients'))->assertOk()->assertSee('Our client directory is being updated.');
        config(['seo.canonical_url' => 'https://hsncoatlaw.com']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://hsncoatlaw.com/clients');
        $this->get('/sitemap')->assertOk()->assertSee(route('clients'));
    }

    public function test_carousel_rows_have_disjoint_logos_and_keep_every_unique_logo(): void
    {
        for ($index=0; $index<7; $index++) {
            $logo=Media::factory()->create(['disk'=>'frontend','path'=>'images/unique-logo-'.$index.'.png','visibility'=>'public']);
            Client::create(['name'=>'Unique logo '.$index,'slug'=>'unique-logo-'.$index,'category'=>'finance','image_id'=>$logo->id,'status'=>'published','published_at'=>now(),'sort_order'=>$index]);
        }
        Client::create(['name'=>'Shared logo','slug'=>'shared-logo','category'=>'finance','image_id'=>$logo->id,'status'=>'published','published_at'=>now()]);
        foreach (['/','/about'] as $url) {
            $html=$this->get($url)->assertOk()->getContent();
            $dom=new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath=new \DOMXPath($dom);
            $rows=$xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," clients-rail ")]');
            $this->assertCount(2,$rows);
            $logos=[];
            foreach($rows as $row) {
                $sources=[];
                foreach($xpath->query('.//img',$row) as $image) {
                    $sources[]=$image->getAttribute('src');
                    $this->assertNotEmpty($image->getAttribute('alt'));
                }
                $logos[]=$sources;
            }
            $this->assertCount(4,$logos[0]);
            $this->assertCount(3,$logos[1]);
            $this->assertSame([],array_intersect($logos[0],$logos[1]));
            $this->assertCount(7,array_unique(array_merge(...$logos)));
        }
    }
}
