<?php

namespace Tests\Feature;

use App\Mail\DealCreatedForClientMail;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DealReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_create_deal_client_gets_email_and_can_leave_review_after_completion(): void
    {
        Mail::fake();

        $provider = User::factory()->create();
        $client = User::factory()->create([
            'email' => 'client@example.com',
        ]);

        $businessProfile = BusinessProfile::factory()->for($provider)->create([
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        $category = Category::factory()->create();

        $offer = Offer::factory()
            ->for($businessProfile)
            ->for($category)
            ->create([
                'is_active' => true,
            ]);

        $this->actingAs($provider);

        $response = $this->post(route('dashboard.deals.store', $businessProfile), [
            'client_email' => strtoupper($client->email), // Ensure normalizer handles casing.
            'offer_id' => (string) $offer->id,
            'agreed_price' => '1500',
            'currency' => 'uah',
            'status' => Deal::STATUS_DRAFT,
        ]);

        $deal = Deal::query()->firstOrFail();

        $response->assertRedirect(route('dashboard.deals.show', [$businessProfile, $deal]));

        $this->assertSame($client->id, $deal->client_user_id);
        $this->assertSame($businessProfile->id, $deal->business_profile_id);
        $this->assertSame($offer->id, $deal->offer_id);
        $this->assertSame(1500, $deal->agreed_price);
        $this->assertSame('UAH', $deal->currency);
        $this->assertSame(Deal::STATUS_DRAFT, $deal->status);

        Mail::assertQueued(DealCreatedForClientMail::class, function (DealCreatedForClientMail $mailable) use ($client) {
            return $mailable->hasTo($client->email);
        });

        // Provider completes the deal.
        $this->patch(route('dashboard.deals.completed', [$businessProfile, $deal]))
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame(Deal::STATUS_COMPLETED, $deal->status);
        $this->assertNotNull($deal->completed_at);

        // Client can leave a review only after completion.
        $this->actingAs($client);

        $this->get(route('reviews.create', $deal))
            ->assertOk();

        $this->post(route('reviews.store', $deal), [
            'rating' => 5,
            'body' => 'Все супер, рекомендую!',
        ])->assertRedirect(route('providers.show', $businessProfile->slug));

        $review = Review::query()->firstOrFail();

        $this->assertSame($deal->id, $review->deal_id);
        $this->assertSame($client->id, $review->client_user_id);
        $this->assertSame($businessProfile->id, $review->business_profile_id);
        $this->assertSame(5, $review->rating);
    }

    public function test_client_cannot_leave_review_before_completion(): void
    {
        $client = User::factory()->create();
        $provider = User::factory()->create();

        $businessProfile = BusinessProfile::factory()->for($provider)->create();

        $deal = Deal::factory()
            ->for($businessProfile, 'businessProfile')
            ->for($client, 'client')
            ->create([
                'status' => Deal::STATUS_IN_PROGRESS,
            ]);

        $this->actingAs($client);

        $this->get(route('reviews.create', $deal))
            ->assertForbidden();

        $this->post(route('reviews.store', $deal), [
            'rating' => 5,
        ])->assertForbidden();
    }
}
