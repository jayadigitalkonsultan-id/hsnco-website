<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class TermsOfServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_terms_are_public_and_link_to_privacy_and_contact(): void
    {
        $this->get(route('terms-of-service'))->assertOk()->assertSee('Terms of Service')->assertSee('lawyer-client relationship')->assertSee(route('privacy-policy'), false)->assertSee(route('contact').'#enquiry', false);
        $this->get(route('home'))->assertOk()->assertSee(route('terms-of-service'), false);
    }
    public function test_terms_are_in_the_public_sitemap(): void
    {
        config(['seo.canonical_url' => 'https://hsncoatlaw.com']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://hsncoatlaw.com/terms-of-service');
    }
}
