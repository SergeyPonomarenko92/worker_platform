<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferPriceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_create_offer_with_only_price_to(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.offers.store', [$profile]), [
                'category_id' => $category->id,
                'type' => 'service',
                'title' => 'Offer with only price_to',
                'description' => 'Test description',
                'price_from' => '',
                'price_to' => 500,
                'currency' => 'uah',
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.index', [$profile]))
            ->assertSessionHas('success');

        $offer = Offer::query()->where('title', 'Offer with only price_to')->first();
        $this->assertNotNull($offer);
        $this->assertNull($offer->price_from);
        $this->assertSame(500, $offer->price_to);
        $this->assertSame('UAH', $offer->currency);
        $this->assertTrue((bool) $offer->is_active);
    }
}
