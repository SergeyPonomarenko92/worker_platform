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
        $response->assertHeader('Cache-Control', 'max-age=300, public');

        $expected = 'Sitemap: '.url('/sitemap.xml');

        $this->assertStringContainsString($expected, $response->getContent());
    }

    public function test_robots_txt_does_not_contain_multiple_sitemap_directives(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Cache-Control', 'max-age=300, public');

        $content = (string) $response->getContent();
        $count = substr_count($content, 'Sitemap:');

        $this->assertSame(1, $count, 'robots.txt should contain exactly one Sitemap directive');
    }

    public function test_robots_txt_normalizes_sitemap_directive_when_static_file_contains_duplicates(): void
    {
        $path = public_path('robots.txt');

        $original = file_exists($path) ? file_get_contents($path) : null;

        try {
            file_put_contents($path, "User-agent: *\nDisallow:\n\nSitemap: /sitemap.xml\nSitemap: https://evil.example/sitemap.xml\n");

            $response = $this->get('/robots.txt');

            $response->assertOk();
            $response->assertHeader('Cache-Control', 'max-age=300, public');

            $content = (string) $response->getContent();

            $this->assertSame(1, substr_count($content, 'Sitemap:'), 'robots.txt should contain exactly one Sitemap directive');
            $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $content);
            $this->assertStringNotContainsString('evil.example', $content);
        } finally {
            if ($original === null) {
                @unlink($path);
            } else {
                file_put_contents($path, $original);
            }
        }
    }
}
