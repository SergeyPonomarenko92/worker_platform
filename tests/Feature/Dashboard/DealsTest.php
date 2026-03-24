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

    public function test_cancelled_deal_clears_completed_at_timestamp(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'status' => 'in_progress',
            // Simulate inconsistent data (manual edits / legacy bug): completed_at set for non-completed deal.
            'completed_at' => now(),
        ]);

        $this->actingAs($provider)
            ->patch(route('dashboard.deals.cancelled', [$profile, $deal]))
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('cancelled', $deal->status);
        $this->assertNull($deal->completed_at);
    }

    public function test_owner_can_create_deal_when_client_email_has_spaces_or_uppercase(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => "  CLIENT@Example.com \n",
                'offer_id' => null,
                'status' => 'draft',
                'currency' => 'uah',
                'agreed_price' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'status' => 'draft',
            'agreed_price' => null,
            'currency' => 'UAH',
        ]);
    }

    public function test_owner_can_create_deal_when_client_email_has_unicode_spaces(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        // NBSP + narrow NBSP around the email/currency.
        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => "\u{00A0}CLIENT@Example.com\u{202F}",
                'offer_id' => null,
                'status' => 'draft',
                'currency' => "\u{00A0}uah\u{202F}",
                'agreed_price' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'status' => 'draft',
            'agreed_price' => null,
            'currency' => 'UAH',
        ]);
    }

    public function test_owner_cannot_create_deal_with_offer_from_another_business_profile(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);
        $otherProfile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $offerFromOtherProfile = Offer::factory()->create(['business_profile_id' => $otherProfile->id]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.create', $profile))
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => $offerFromOtherProfile->id,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect(route('dashboard.deals.create', $profile))
            ->assertSessionHasErrors([
                'offer_id' => 'Офер має належати вибраному профілю бізнесу.',
            ]);

        $this->assertDatabaseMissing('deals', [
            'business_profile_id' => $profile->id,
            'offer_id' => $offerFromOtherProfile->id,
        ]);
    }

    public function test_owner_can_create_deal_when_offer_id_is_empty_string(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => '',
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'offer_id' => null,
            'status' => 'draft',
        ]);
    }

    public function test_deal_creation_sends_email_to_client(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id, 'name' => 'Demo Provider']);

        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => null,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect();

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\DealCreatedForClientMail::class, function ($mailable) use ($client) {
            $this->assertTrue($mailable->hasTo($client->email));

            // Ensure subject is stable and includes provider name.
            $this->assertSame('Нова угода від Demo Provider', $mailable->envelope()->subject);

            // Ensure basic content is rendered and includes key info.
            $html = $mailable->render();

            $this->assertStringContainsString('Нова угода створена', $html);
            $this->assertStringContainsString('Demo Provider', $html);
            $this->assertStringContainsString('Чернетка', $html);
            $this->assertStringContainsString('100 UAH', $html);

            return true;
        });
    }

    public function test_deal_creation_does_not_fail_when_email_sending_fails(): void
    {
        // Robustness: Deal creation should succeed even if email sending fails.
        \Illuminate\Support\Facades\Mail::shouldReceive('to->queue')
            ->once()
            ->andThrow(new \RuntimeException('SMTP down'));

        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($provider)
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => null,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 100,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'business_profile_id' => $profile->id,
            'status' => 'draft',
            'currency' => 'UAH',
            'agreed_price' => 100,
        ]);
    }

    public function test_owner_cannot_create_deal_with_decimal_agreed_price(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.create', $profile))
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => null,
                'status' => 'draft',
                'currency' => 'UAH',
                'agreed_price' => 99.99,
            ])
            ->assertRedirect(route('dashboard.deals.create', $profile))
            ->assertSessionHasErrors(['agreed_price']);

        $this->assertDatabaseCount('deals', 0);
    }

    public function test_owner_cannot_create_deal_with_unknown_currency(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.create', $profile))
            ->post(route('dashboard.deals.store', $profile), [
                'client_email' => $client->email,
                'offer_id' => null,
                'status' => 'draft',
                'currency' => 'RUB',
                'agreed_price' => 100,
            ])
            ->assertRedirect(route('dashboard.deals.create', $profile))
            ->assertSessionHasErrors(['currency']);

        $this->assertDatabaseCount('deals', 0);
    }
}
