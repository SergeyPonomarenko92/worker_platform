<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DealsIndexPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deals_index_does_not_trigger_n_plus_one_queries(): void
    {
        $provider = User::factory()->create();

        $businessProfile = BusinessProfile::factory()->for($provider)->create([
            'is_active' => true,
        ]);

        $offer = Offer::factory()->for($businessProfile)->create([
            'title' => 'Base offer',
            'is_active' => true,
        ]);

        // Enough deals/clients to amplify any accidental per-row loading.
        $clients = User::factory()->count(50)->create();

        foreach ($clients as $client) {
            Deal::factory()->for($businessProfile)->create([
                'client_user_id' => $client->id,
                'offer_id' => $offer->id,
                'status' => 'draft',
            ]);
        }

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this
            ->actingAs($provider)
            ->get('/dashboard/business-profiles/'.$businessProfile->id.'/deals')
            ->assertOk();

        // Query budget guard: DealController@index should eager-load client + offer.
        $this->assertLessThanOrEqual(12, $queries);
    }
}
