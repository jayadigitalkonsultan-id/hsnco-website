<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentProxyTest extends TestCase
{
    public function test_https_urls_are_generated_behind_an_explicitly_trusted_proxy(): void
    {
        $previous = getenv('TRUSTED_PROXIES');
        putenv('TRUSTED_PROXIES=*');
        $_ENV['TRUSTED_PROXIES'] = '*';
        $_SERVER['TRUSTED_PROXIES'] = '*';

        try {
            $this->refreshApplication();
            $this->withHeaders([
                'Host' => 'hsncoatlaw.com',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Port' => '443',
            ])->get('http://hsncoatlaw.com/')
                ->assertOk()
                ->assertSee('https://hsncoatlaw.com/assets/vendor/bootstrap/css/bootstrap.min.css', false)
                ->assertSee('https://hsncoatlaw.com/about', false);

            $this->get('http://hsncoatlaw.com/route-not-present')->assertNotFound();
        } finally {
            if ($previous === false) {
                putenv('TRUSTED_PROXIES');
                unset($_ENV['TRUSTED_PROXIES'], $_SERVER['TRUSTED_PROXIES']);
            } else {
                putenv('TRUSTED_PROXIES='.$previous);
                $_ENV['TRUSTED_PROXIES'] = $previous;
                $_SERVER['TRUSTED_PROXIES'] = $previous;
            }
        }
    }
}