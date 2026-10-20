<?php
namespace Tests\Feature;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ContactMapTest extends TestCase
{
 use RefreshDatabase;
 public function test_map_uses_owner_address_and_offers_external_link(): void
 {
  $this->get(route('contact'))->assertOk()->assertSee('lokasi-kantor')->assertSee('maps.google.com/maps?q=',false)->assertSee('Peta alamat kantor')->assertSee('Buka di Google Maps')->assertSee(config('contact-map.address'))->assertSee('https://www.google.com/maps/search/?api=1&amp;query=',false);
 }
 public function test_verified_cms_address_takes_priority_over_default(): void
 {
  SiteSetting::create(['key'=>'contact.details','group'=>'contact','value'=>['verified'=>true,'office_address'=>'Alamat kantor terbaru dari CMS']]);
  $this->get(route('contact'))->assertOk()->assertSee('Alamat kantor terbaru dari CMS')->assertSee(rawurlencode('Alamat kantor terbaru dari CMS'),false)->assertDontSee(config('contact-map.address'));
 }
}