<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function test_robots_txt_is_served_as_plain_text_and_contains_absolute_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = (string) $response->getContent();

        $this->assertStringContainsString("User-agent:", $content);
        $this->assertStringContainsString('Sitemap: http://localhost/sitemap.xml', $content);

        // Route should normalize relative sitemap directives from the public file.
        $this->assertStringNotContainsString('Sitemap: /sitemap.xml', $content);
    }
}
