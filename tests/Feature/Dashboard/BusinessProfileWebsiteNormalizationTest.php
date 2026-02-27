<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileWebsiteNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_website_is_normalized_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'website' => 'example.com',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('https://example.com', $profile->website);
    }

    public function test_business_profile_website_is_trimmed_on_store_and_keeps_http_scheme_case(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'website' => '  HTTP://foo.test  ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('HTTP://foo.test', $profile->website);
    }

    public function test_business_profile_website_normalizes_unicode_spaces_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'website' => "\u{00A0}example.com\u{202F}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('https://example.com', $profile->website);
    }

    public function test_empty_business_profile_website_becomes_null_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'website' => '   ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertNull($profile->website);
    }

    public function test_business_profile_website_is_normalized_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'website' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'website' => ' https://foo.test ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('https://foo.test', $profile->website);
    }

    public function test_business_profile_website_with_http_scheme_is_not_double_prefixed_even_if_uppercase(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'website' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'website' => 'HTTP://foo.test',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('HTTP://foo.test', $profile->website);
    }

    public function test_empty_business_profile_website_becomes_null(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'website' => 'https://already.test',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'website' => '   ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertNull($profile->website);
    }

    public function test_business_profile_website_must_be_http_or_https_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'website' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors(['website']);

        $this->assertDatabaseMissing('business_profiles', [
            'user_id' => $user->id,
            'name' => 'Test Provider',
        ]);
    }
}
