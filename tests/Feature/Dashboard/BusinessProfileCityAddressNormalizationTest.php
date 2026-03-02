<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileCityAddressNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_city_and_address_are_trimmed_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'city' => '  Київ  ',
                'address' => '  вул. Хрещатик, 1  ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('Київ', $profile->city);
        $this->assertSame('вул. Хрещатик, 1', $profile->address);
    }

    public function test_business_profile_city_and_address_normalize_unicode_spaces_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'city' => "\u{00A0}Київ\u{202F}",
                'address' => "\u{00A0}вул.\u{202F}Хрещатик\u{00A0}1\u{00A0}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('Київ', $profile->city);
        $this->assertSame('вул. Хрещатик 1', $profile->address);
    }

    public function test_empty_business_profile_city_and_address_become_null_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'city' => '   ',
                'address' => "\u{00A0}\u{202F}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertNull($profile->city);
        $this->assertNull($profile->address);
    }

    public function test_business_profile_city_and_address_are_trimmed_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'city' => null,
            'address' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'city' => '  Львів  ',
                'address' => '  пл. Ринок 10  ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('Львів', $profile->city);
        $this->assertSame('пл. Ринок 10', $profile->address);
    }

    public function test_empty_business_profile_city_and_address_become_null_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'city' => 'Київ',
            'address' => 'Some address',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'city' => '   ',
                'address' => "\u{00A0}\u{202F}",
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertNull($profile->city);
        $this->assertNull($profile->address);
    }
}
