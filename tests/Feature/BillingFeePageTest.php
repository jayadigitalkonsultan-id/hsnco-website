<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFeePageTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_page_displays_all_six_schemes_and_illustrations(): void
    {
        $response=$this->get(route('billing-fees'))->assertOk()->assertSee('Billing &amp; Fees',false)->assertSee('skema-biaya');
        foreach(config('billing.fees') as $fee)$response->assertSee($fee['name'])->assertSee($fee['description']);
        $this->assertSame(6,substr_count($response->getContent(),'class="fee-card h-100"'));
        $this->assertSame(6,substr_count($response->getContent(),'viewBox="0 0 120 100"'));
        $response->assertSee(route('contact').'#enquiry')->assertSee(route('team'));
    }
    public function test_new_page_is_linked_from_professionals_practice_areas_and_sitemap(): void
    {
        foreach(['team','practice-areas','sitemap'] as $route)$this->get(route($route))->assertOk()->assertSee(route('billing-fees'));
        config(['seo.canonical_url'=>'https://hsncoatlaw.com']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://hsncoatlaw.com/billing-fees');
    }
}