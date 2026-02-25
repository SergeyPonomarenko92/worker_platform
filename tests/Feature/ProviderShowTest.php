<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\PortfolioPost;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProviderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_page_is_not_accessible_for_inactive_provider(): void
    {
        $provider = BusinessProfile::factory()->create([
            'slug' => 'inactive-provider',
            'is_active' => false,
        ]);

        $this->get('/providers/'.$provider->slug)->assertNotFound();
    }

    public function test_provider_page_includes_offer_category(): void
    {
        $category = Category::factory()->create(['name' => 'Електрика']);
        $provider = BusinessProfile::factory()->create(['slug' => 'demo-provider']);

        Offer::factory()->for($provider)->create([
            'category_id' => $category->id,
            'title' => 'Demo offer',
            'is_active' => true,
        ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->has('provider.offers', 1)
                ->where('provider.offers.0.title', 'Demo offer')
                ->where('provider.offers.0.category.name', 'Електрика')
            );
    }

    public function test_provider_page_shows_only_published_portfolio_posts(): void
    {
        Carbon::setTestNow(now());

        $provider = BusinessProfile::factory()->create(['slug' => 'demo-provider', 'is_active' => true]);

        PortfolioPost::factory()->for($provider)->create([
            'title' => 'Published',
            'published_at' => now()->subDay(),
        ]);

        PortfolioPost::factory()->for($provider)->draft()->create([
            'title' => 'Draft',
        ]);

        PortfolioPost::factory()->for($provider)->create([
            'title' => 'Scheduled',
            'published_at' => now()->addDay(),
        ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('provider.published_portfolio_posts_count', 1)
                ->where('provider.portfolio_posts', function ($posts) {
                    $posts = $posts instanceof \Illuminate\Support\Collection ? $posts->all() : (array) $posts;
                    $titles = array_map(fn ($p) => $p['title'] ?? null, $posts);
                    sort($titles);

                    return $titles === ['Published'];
                })
            );
    }

    public function test_provider_page_shows_only_not_expired_stories(): void
    {
        Carbon::setTestNow(now());

        $provider = BusinessProfile::factory()->create(['slug' => 'demo-provider', 'is_active' => true]);

        Story::factory()->for($provider)->create([
            'caption' => 'Active story',
            'expires_at' => now()->addHour(),
        ]);

        Story::factory()->for($provider)->create([
            'caption' => 'Expired story',
            'expires_at' => now()->subHour(),
        ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->has('provider.stories', 1)
                ->where('provider.stories.0.caption', 'Active story')
            );
    }
}
