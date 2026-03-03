<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapXmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_sitemap_xml_even_when_there_is_no_content_yet(): void
    {
        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertSee('<urlset', false)
            ->assertSee(url('/catalog'), false);

        // With an empty DB, lastmod may be omitted.
        $response->assertDontSee('<lastmod>', false);
    }

    public function test_it_serves_sitemap_xml_with_absolute_urls(): void
    {
        $latestProviderUpdatedAt = now();
        $provider = BusinessProfile::factory()->create([
            'slug' => 'demo-provider-for-sitemap',
            'is_active' => true,
            'updated_at' => $latestProviderUpdatedAt,
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
            // Catalog lastmod should reflect the latest update among offers OR providers.
            ->assertSee('<lastmod>'.$latestProviderUpdatedAt->toDateString().'</lastmod>', false)
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

    public function test_catalog_lastmod_ignores_offers_of_inactive_providers(): void
    {
        $inactiveProvider = BusinessProfile::factory()->create([
            'slug' => 'inactive-provider',
            'is_active' => false,
        ]);

        // This offer is invisible in the catalog because its provider is inactive.
        Offer::factory()->for($inactiveProvider)->create([
            'is_active' => true,
            'updated_at' => now(),
        ]);

        $activeProvider = BusinessProfile::factory()->create([
            'slug' => 'active-provider',
            'is_active' => true,
            'updated_at' => now()->subDays(3),
        ]);

        $visibleOfferUpdatedAt = now()->subDay();
        Offer::factory()->for($activeProvider)->create([
            'is_active' => true,
            'updated_at' => $visibleOfferUpdatedAt,
        ]);

        $response = $this->get('/sitemap.xml');

        // Catalog lastmod should be the latest among visible offers or active providers.
        $response
            ->assertOk()
            ->assertSee('<loc>'.url('/catalog').'</loc>', false)
            ->assertSee('<lastmod>'.$visibleOfferUpdatedAt->toDateString().'</lastmod>', false);
    }
}
