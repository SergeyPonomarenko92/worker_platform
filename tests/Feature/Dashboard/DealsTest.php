<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_update_deal_statuses(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);
        $offer = Offer::factory()->create(['business_profile_id' => $profile->id]);

        // Create
        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => $offer->id,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect();

        $deal = Deal::query()->where('business_profile_id', $profile->id)->firstOrFail();
        $this->assertSame('draft', $deal->status);

        // Mark in progress
        $this->actingAs($provider)
            ->patch(route('dashboard.deals.in-progress', [$profile, $deal]))
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('in_progress', $deal->status);

        // Mark cancelled (allowed from in_progress)
        $this->actingAs($provider)
            ->patch(route('dashboard.deals.cancelled', [$profile, $deal]))
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('cancelled', $deal->status);

        // Create a second deal to test completion path
        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => $offer->id,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect();

        $deal2 = Deal::query()
            ->where('business_profile_id', $profile->id)
            ->where('status', 'draft')
            ->latest('id')
            ->firstOrFail();

        // Mark completed
        $this->actingAs($provider)
            ->patch(route('dashboard.deals.completed', [$profile, $deal2]))
            ->assertRedirect();

        $deal2->refresh();
        $this->assertSame('completed', $deal2->status);
        $this->assertNotNull($deal2->completed_at);
    }
}
