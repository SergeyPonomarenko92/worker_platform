<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function test_it_serves_robots_txt(): void
    {
        $response = $this->get('/robots.txt');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertSee("User-agent: *")
            ->assertSee('Disallow: /dashboard/')
            ->assertSee('Disallow: /login')
            ->assertSee('Allow: /')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }
}
