<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_leave_review_only_for_completed_deal_and_only_once(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create();

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'client_user_id' => $client->id,
            'business_profile_id' => $profile->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Create
        $this->actingAs($client)
            ->post(route('reviews.store', $deal), [
                'rating' => 5,
                'body' => 'Все супер!',
            ])
            ->assertRedirect(route('providers.show', $profile->slug));

        $this->assertDatabaseHas('reviews', [
            'deal_id' => $deal->id,
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'rating' => 5,
        ]);

        // Cannot create second review for same deal
        $this->actingAs($client)
            ->post(route('reviews.store', $deal), [
                'rating' => 4,
                'body' => 'Другий раз',
            ])
            ->assertForbidden();

        $this->assertSame(1, Review::query()->where('deal_id', $deal->id)->count());
    }

    public function test_other_user_cannot_leave_review_for_someone_elses_deal(): void
    {
        $client = User::factory()->create();
        $other = User::factory()->create();

        $profile = BusinessProfile::factory()->create();

        $deal = Deal::factory()->create([
            'client_user_id' => $client->id,
            'business_profile_id' => $profile->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($other)
            ->post(route('reviews.store', $deal), [
                'rating' => 5,
            ])
            ->assertForbidden();
    }

    public function test_client_cannot_leave_review_for_not_completed_deal(): void
    {
        $client = User::factory()->create();
        $profile = BusinessProfile::factory()->create();

        $deal = Deal::factory()->create([
            'client_user_id' => $client->id,
            'business_profile_id' => $profile->id,
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        $this->actingAs($client)
            ->post(route('reviews.store', $deal), [
                'rating' => 5,
            ])
            ->assertForbidden();
    }
}
