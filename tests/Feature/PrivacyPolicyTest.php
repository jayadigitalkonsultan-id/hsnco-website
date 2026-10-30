<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PrivacyPolicyTest extends TestCase
{
    use RefreshDatabase;
    public function test_policy_is_public_and_explains_google_data_handling(): void
    {
        $this->get(route('privacy-policy'))->assertOk()->assertSee('gmail.send')->assertSee('Limited Use')->assertSee('Google Account connections')->assertSee('privacy-retention')->assertSee(route('contact').'#enquiry', false);
        $this->get(route('home'))->assertOk()->assertSee(route('privacy-policy'), false);
    }
    public function test_policy_is_in_the_xml_sitemap(): void
    {
        config(['seo.canonical_url' => 'https://hsncoatlaw.com']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://hsncoatlaw.com/privacy-policy');
    }
}
