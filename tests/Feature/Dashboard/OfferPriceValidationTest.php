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
                'currency' => '  uah  ',
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

    public function test_provider_can_update_offer_currency_with_spaces(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offer = Offer::factory()->for($profile)->create([
            'currency' => 'UAH',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.offers.update', [$profile, $offer]), [
                'category_id' => $offer->category_id,
                'type' => $offer->type,
                'title' => $offer->title,
                'description' => $offer->description,
                'price_from' => $offer->price_from,
                'price_to' => $offer->price_to,
                'currency' => '  usd  ',
                'is_active' => $offer->is_active ? 1 : 0,
            ])
            ->assertRedirect();

        $offer->refresh();
        $this->assertSame('USD', $offer->currency);
    }

    public function test_provider_cannot_create_offer_when_price_to_is_less_than_price_from(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.offers.create', [$profile]))
            ->post(route('dashboard.offers.store', [$profile]), [
                'category_id' => $category->id,
                'type' => 'service',
                'title' => 'Invalid price range',
                'description' => 'Test description',
                'price_from' => 100,
                'price_to' => 50,
                'currency' => 'UAH',
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.create', [$profile]))
            ->assertSessionHasErrors(['price_to']);

        $this->assertDatabaseMissing('offers', [
            'title' => 'Invalid price range',
        ]);
    }

    public function test_provider_cannot_update_offer_when_price_to_is_less_than_price_from(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offer = Offer::factory()->for($profile)->create([
            'price_from' => 100,
            'price_to' => 150,
        ]);

        $this->actingAs($user)
            ->from(route('dashboard.offers.edit', [$profile, $offer]))
            ->patch(route('dashboard.offers.update', [$profile, $offer]), [
                'category_id' => $offer->category_id,
                'type' => $offer->type,
                'title' => $offer->title,
                'description' => $offer->description,
                'price_from' => 200,
                'price_to' => 100,
                'currency' => $offer->currency,
                'is_active' => $offer->is_active ? 1 : 0,
            ])
            ->assertRedirect(route('dashboard.offers.edit', [$profile, $offer]))
            ->assertSessionHasErrors(['price_to']);

        $offer->refresh();
        $this->assertSame(100, $offer->price_from);
        $this->assertSame(150, $offer->price_to);
    }
}
