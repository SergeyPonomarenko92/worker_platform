<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function test_robots_txt_includes_absolute_sitemap_directive(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $expected = 'Sitemap: '.url('/sitemap.xml');

        $this->assertStringContainsString($expected, $response->getContent());
    }

    public function test_robots_txt_does_not_contain_multiple_sitemap_directives(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();

        $content = (string) $response->getContent();
        $count = substr_count($content, 'Sitemap:');

        $this->assertSame(1, $count, 'robots.txt should contain exactly one Sitemap directive');
    }
}
