<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapXmlTest extends TestCase
{
    public function test_it_serves_sitemap_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<?xml', false);
        $response->assertSee('<urlset', false);
    }
}
