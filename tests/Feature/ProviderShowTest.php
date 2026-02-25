<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\PortfolioPost;
use App\Models\Review;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProviderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_page_loads_limited_portfolio_and_reviews_by_default_and_can_load_all_via_query_params(): void
    {
        Carbon::setTestNow(now());

        $provider = BusinessProfile::factory()->create([
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        PortfolioPost::factory()->for($provider)->count(80)->create([
            'published_at' => now()->subDay(),
        ]);

        // Create enough completed deals + reviews so that the controller needs to apply limits.
        // Use a single client to keep the test fast (we only care about controller limits/counts here).
        $client = User::factory()->create();

        $reviewsCount = 30;
        for ($i = 0; $i < $reviewsCount; $i++) {
            $deal = Deal::factory()->create([
                'business_profile_id' => $provider->id,
                'client_user_id' => $client->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Review::factory()->create([
                'deal_id' => $deal->id,
                'business_profile_id' => $provider->id,
                'client_user_id' => $client->id,
            ]);
        }

        // Default response should include counts for the full data, but only preload limited lists.
        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('provider.published_portfolio_posts_count', 80)
                ->where('provider.reviews_count', 30)
                ->has('provider.portfolio_posts', 60)
                ->has('provider.reviews', 20)
            );

        // When explicitly requested, controller should preload more items (up to 200).
        $this
            ->get('/providers/'.$provider->slug.'?all_portfolio=1&all_reviews=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('provider.published_portfolio_posts_count', 80)
                ->where('provider.reviews_count', 30)
                ->has('provider.portfolio_posts', 80)
                ->has('provider.reviews', 30)
                ->where('loadAllPortfolio', true)
                ->where('loadAllReviews', true)
            );
    }

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

    public function test_provider_page_includes_offers_and_reviews_stats(): void
    {
        $providerOwner = User::factory()->create();
        $client = User::factory()->create();

        $provider = BusinessProfile::factory()->create([
            'user_id' => $providerOwner->id,
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        // Offers count should include only active offers
        Offer::factory()->for($provider)->create(['is_active' => true]);
        Offer::factory()->for($provider)->create(['is_active' => false]);

        $deal1 = Deal::factory()->create([
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $deal2 = Deal::factory()->create([
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Review::factory()->create([
            'deal_id' => $deal1->id,
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'deal_id' => $deal2->id,
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'rating' => 5,
        ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('provider.offers_count', 1)
                ->where('provider.reviews_count', 2)
                ->where('provider.reviews_avg_rating', fn ($avg) => abs(((float) $avg) - 4.5) < 0.0001)
            );
    }

    public function test_provider_page_sets_eligible_deal_id_for_logged_in_client_without_review(): void
    {
        $providerOwner = User::factory()->create();
        $client = User::factory()->create();

        $provider = BusinessProfile::factory()->create([
            'user_id' => $providerOwner->id,
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        $completedWithReview = Deal::factory()->create([
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'status' => 'completed',
            'completed_at' => now()->subDays(2),
        ]);

        Review::factory()->create([
            'deal_id' => $completedWithReview->id,
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'rating' => 5,
        ]);

        $completedWithoutReview = Deal::factory()->create([
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        // Newest eligible deal should be suggested.
        $newestCompletedWithoutReview = Deal::factory()->create([
            'business_profile_id' => $provider->id,
            'client_user_id' => $client->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this
            ->actingAs($client)
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('eligibleDealId', $newestCompletedWithoutReview->id)
            );

        $this->assertNotEquals($completedWithoutReview->id, $newestCompletedWithoutReview->id);
    }

    public function test_provider_page_can_load_all_portfolio_posts_via_query_param(): void
    {
        Carbon::setTestNow(now());

        $provider = BusinessProfile::factory()->create([
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        // More than the default preload limit (60)
        PortfolioPost::factory()
            ->count(65)
            ->for($provider)
            ->create([
                'published_at' => now()->subDay(),
            ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('loadAllPortfolio', false)
                ->where('provider.published_portfolio_posts_count', 65)
                ->has('provider.portfolio_posts', 60)
            );

        $this
            ->get('/providers/'.$provider->slug.'?all_portfolio=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('loadAllPortfolio', true)
                ->where('provider.published_portfolio_posts_count', 65)
                ->has('provider.portfolio_posts', 65)
            );
    }

    public function test_provider_page_can_load_all_reviews_via_query_param(): void
    {
        Carbon::setTestNow(now());

        $providerOwner = User::factory()->create();
        $provider = BusinessProfile::factory()->create([
            'user_id' => $providerOwner->id,
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        $client = User::factory()->create();

        // More than the default preload limit (20)
        foreach (range(1, 25) as $i) {
            $deal = Deal::factory()->create([
                'business_profile_id' => $provider->id,
                'client_user_id' => $client->id,
                'status' => 'completed',
                'completed_at' => now()->subMinutes($i),
            ]);

            Review::factory()->create([
                'deal_id' => $deal->id,
                'business_profile_id' => $provider->id,
                'client_user_id' => $client->id,
                'rating' => 5,
                'body' => 'Good job #'.$i,
            ]);
        }

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('loadAllReviews', false)
                ->where('provider.reviews_count', 25)
                ->has('provider.reviews', 20)
            );

        $this
            ->get('/providers/'.$provider->slug.'?all_reviews=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->where('loadAllReviews', true)
                ->where('provider.reviews_count', 25)
                ->has('provider.reviews', 25)
            );
    }
}
