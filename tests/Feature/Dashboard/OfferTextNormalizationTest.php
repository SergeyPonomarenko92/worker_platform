<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTextNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_title_is_trimmed_and_whitespace_collapsed_on_store(): void
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
                'title' => "  My\t\n Offer  ",
                'description' => "  Some\n\t description  ",
                'price_from' => '',
                'price_to' => '',
                'currency' => 'uah',
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.index', [$profile]))
            ->assertSessionHas('success');

        $offer = Offer::query()->where('business_profile_id', $profile->id)->latest('id')->firstOrFail();

        $this->assertSame('My Offer', $offer->title);
        $this->assertSame('Some description', $offer->description);
    }

    public function test_offer_currency_allows_extra_whitespace_on_store(): void
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
                'title' => 'Test Offer',
                'description' => 'desc',
                'price_from' => '',
                'price_to' => '',
                'currency' => "\u{00A0} u\t a\n h \u{202F}",
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.index', [$profile]))
            ->assertSessionHas('success');

        $offer = Offer::query()->where('business_profile_id', $profile->id)->latest('id')->firstOrFail();

        $this->assertSame('UAH', $offer->currency);
    }

    public function test_offer_currency_invalid_value_is_rejected_on_store(): void
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
                'title' => 'Test Offer',
                'description' => 'desc',
                'price_from' => '',
                'price_to' => '',
                'currency' => 'btc',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['currency']);

        $this->assertDatabaseCount('offers', 0);
    }

    public function test_offer_description_empty_string_becomes_null_on_store(): void
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
                'title' => 'Test Offer',
                'description' => "\u{00A0}   \u{202F}",
                'price_from' => '',
                'price_to' => '',
                'currency' => 'uah',
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.index', [$profile]));

        $offer = Offer::query()->where('business_profile_id', $profile->id)->latest('id')->firstOrFail();
        $this->assertNull($offer->description);
    }

    public function test_offer_title_whitespace_only_is_rejected_on_store(): void
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
                'title' => "\u{00A0}   \n\t\u{202F}",
                'description' => 'desc',
                'price_from' => '',
                'price_to' => '',
                'currency' => 'uah',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['title']);

        $this->assertDatabaseCount('offers', 0);
    }

    public function test_offer_description_empty_string_becomes_null_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offer = Offer::factory()->for($profile)->create([
            'description' => 'Already has text',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.offers.update', [$profile, $offer]), [
                'category_id' => $offer->category_id,
                'type' => $offer->type,
                'title' => $offer->title,
                'description' => '   ',
                'price_from' => $offer->price_from,
                'price_to' => $offer->price_to,
                'currency' => $offer->currency,
                'is_active' => $offer->is_active ? 1 : 0,
            ])
            ->assertRedirect();

        $offer->refresh();
        $this->assertNull($offer->description);
    }

    public function test_offer_currency_allows_extra_whitespace_on_update(): void
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
                'currency' => "  u\t a\n h  ",
                'is_active' => $offer->is_active ? 1 : 0,
            ])
            ->assertRedirect();

        $offer->refresh();
        $this->assertSame('UAH', $offer->currency);
    }

    public function test_offer_type_is_case_insensitive_and_trimmed_on_store(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.offers.store', [$profile]), [
                'category_id' => $category->id,
                'type' => "  SeRvIcE\u{00A0}",
                'title' => 'Test Offer',
                'description' => 'desc',
                'price_from' => '',
                'price_to' => '',
                'currency' => 'uah',
                'is_active' => 1,
            ])
            ->assertRedirect(route('dashboard.offers.index', [$profile]))
            ->assertSessionHas('success');

        $offer = Offer::query()->where('business_profile_id', $profile->id)->latest('id')->firstOrFail();
        $this->assertSame('service', $offer->type);
    }

    public function test_offer_type_is_case_insensitive_and_trimmed_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $offer = Offer::factory()->for($profile)->create([
            'type' => 'product',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.offers.update', [$profile, $offer]), [
                'category_id' => $offer->category_id,
                'type' => "\u{202F} PrOdUcT ",
                'title' => $offer->title,
                'description' => $offer->description,
                'price_from' => $offer->price_from,
                'price_to' => $offer->price_to,
                'currency' => $offer->currency,
                'is_active' => $offer->is_active ? 1 : 0,
            ])
            ->assertRedirect();

        $offer->refresh();
        $this->assertSame('product', $offer->type);
    }
}
