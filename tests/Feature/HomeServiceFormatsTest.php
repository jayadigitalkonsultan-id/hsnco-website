<?php
namespace Tests\Feature;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class HomeServiceFormatsTest extends TestCase
{
 use RefreshDatabase;
 public function test_fallback_uses_actual_service_formats_instead_of_generic_sectors(): void
 {
  $response=$this->get('/')->assertOk()->assertSee(config('service-formats.heading'))->assertDontSee('Jasa Keuangan &amp; Teknologi Finansial',false);
  foreach(config('service-formats.items') as $item)$response->assertSee($item['title'])->assertSee($item['description']);
 }
 public function test_legacy_cms_defaults_are_replaced_without_overwriting_custom_editorial_content(): void
 {
  $page=Page::factory()->create(['key'=>'home','slug'=>'home','template'=>'home','status'=>'published','published_at'=>now()->subMinute()]);
  $section=$page->sections()->create(['key'=>'sectors','type'=>'sectors','heading'=>'Memahami dunia usaha Anda.','sort_order'=>1,'is_visible'=>true,'content'=>['items'=>collect(config('service-formats.legacy_titles'))->map(fn($title)=>['title'=>$title,'description'=>'Lama'])->all()]]);
  $this->get('/')->assertOk()->assertSee(config('service-formats.heading'))->assertSee('Nasihat Hukum');
  $section->update(['heading'=>'Layanan pilihan klien','content'=>['eyebrow'=>'Pilihan','description'=>'Narasi hasil edit','items'=>[['title'=>'Layanan khusus klien','description'=>'Isi khusus klien']]]]);
  $this->get('/')->assertOk()->assertSee('Layanan pilihan klien')->assertSee('Layanan khusus klien')->assertDontSee(config('service-formats.heading'));
  $section->update(['is_visible'=>false]);$this->get('/')->assertOk()->assertDontSee('data-service-formats',false);
 }
}