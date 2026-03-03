<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Offer;
use Tests\TestCase;

class SitemapXmlTest extends TestCase
{
    public function test_it_serves_sitemap_xml_with_absolute_urls(): void
    {
        $provider = BusinessProfile::factory()->create([
            'slug' => 'demo-provider-for-sitemap',
            'is_active' => true,
            'updated_at' => now()->subDays(2),
        ]);

        $latestOfferUpdatedAt = now()->subDay();
        Offer::factory()->create([
            'is_active' => true,
            'updated_at' => $latestOfferUpdatedAt,
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertSee('<urlset', false)
            ->assertSee(url('/catalog'), false)
            ->assertSee('<lastmod>'.$latestOfferUpdatedAt->toDateString().'</lastmod>', false)
            ->assertSee(url('/providers/'.$provider->slug), false)
            ->assertSee('<lastmod>'.$provider->updated_at->toDateString().'</lastmod>', false);
    }

    public function test_it_does_not_include_inactive_providers(): void
    {
        BusinessProfile::factory()->create([
            'slug' => 'inactive-provider-for-sitemap',
            'is_active' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertDontSee('inactive-provider-for-sitemap');
    }
}
