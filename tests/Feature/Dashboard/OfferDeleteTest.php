<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_offer(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offer = Offer::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($user)
            ->delete(route('dashboard.offers.destroy', [$profile, $offer]))
            ->assertRedirect(route('dashboard.offers.index', $profile));

        $this->assertDatabaseMissing('offers', [
            'id' => $offer->id,
        ]);
    }

    public function test_user_cannot_delete_offer_from_other_users_profile(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $offer = Offer::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($attacker)
            ->delete(route('dashboard.offers.destroy', [$profile, $offer]))
            ->assertForbidden();

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
        ]);
    }
}
