<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FullUserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_happy_path_provider_creates_profile_offer_deal_and_client_leaves_review(): void
    {
        Mail::fake();

        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        // Provider creates a business profile.
        $this->actingAs($provider)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Demo Provider',
                'country_code' => 'UA',
                'city' => 'Київ',
                'is_active' => true,
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()
            ->where('user_id', $provider->id)
            ->where('name', 'Demo Provider')
            ->firstOrFail();

        // Provider creates an offer in that profile.
        $this->actingAs($provider)
            ->post(route('dashboard.offers.store', $profile), [
                'category_id' => null,
                'type' => 'service',
                'title' => 'Ремонт ноутбуків',
                'description' => 'Швидко та якісно.',
                'price_from' => 100,
                'price_to' => 200,
                'currency' => 'UAH',
                'is_active' => true,
            ])
            ->assertRedirect();

        $offer = Offer::query()
            ->where('business_profile_id', $profile->id)
            ->where('title', 'Ремонт ноутбуків')
            ->firstOrFail();

        // Provider creates a deal for the client.
        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => $offer->id,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 150,
            ])
            ->assertRedirect();

        $deal = Deal::query()
            ->where('business_profile_id', $profile->id)
            ->where('client_user_id', $client->id)
            ->latest('id')
            ->firstOrFail();

        // Provider completes the deal.
        $this->actingAs($provider)
            ->patch(route('dashboard.deals.completed', [$profile, $deal]))
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('completed', $deal->status);
        $this->assertNotNull($deal->completed_at);

        // Client leaves a review.
        $this->actingAs($client)
            ->post(route('reviews.store', $deal), [
                'rating' => 5,
                'body' => 'Все супер, рекомендую!',
            ])
            ->assertRedirect(route('providers.show', $profile->slug));

        $review = Review::query()->where('deal_id', $deal->id)->firstOrFail();
        $this->assertSame(5, $review->rating);
        $this->assertSame($client->id, $review->client_user_id);
        $this->assertSame($profile->id, $review->business_profile_id);

        // Provider public page should load successfully after the flow.
        $this->get(route('providers.show', $profile->slug))
            ->assertOk();
    }
}
