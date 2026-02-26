<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferScopeBindingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_routes_return_404_when_offer_does_not_belong_to_business_profile(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProfile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offerFromOtherProfile = Offer::factory()->create([
            'business_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.offers.edit', [$profile, $offerFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.offers.update', [$profile, $offerFromOtherProfile]), [
                'category_id' => $offerFromOtherProfile->category_id,
                'type' => $offerFromOtherProfile->type,
                'title' => $offerFromOtherProfile->title,
                'description' => $offerFromOtherProfile->description,
                'price_from' => $offerFromOtherProfile->price_from,
                'price_to' => $offerFromOtherProfile->price_to,
                'currency' => $offerFromOtherProfile->currency,
                'is_active' => $offerFromOtherProfile->is_active ? 1 : 0,
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('dashboard.offers.destroy', [$profile, $offerFromOtherProfile]))
            ->assertNotFound();
    }
}
