<?php

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\PortfolioPost;
use App\Models\Review;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::query()->firstOrCreate(
            ['email' => 'client@example.com'],
            ['name' => 'Client', 'password' => 'password'],
        );

        $providerUser = User::query()->firstOrCreate(
            ['email' => 'provider@example.com'],
            ['name' => 'Provider', 'password' => 'password'],
        );

        $provider = BusinessProfile::query()->firstOrCreate(
            ['slug' => 'demo-provider'],
            [
                'user_id' => $providerUser->id,
                'name' => 'Demo Provider',
                'about' => 'Демо-профіль провайдера для тестування каталогу.',
                'country_code' => 'UA',
                'city' => 'Київ',
                'address' => null,
                'phone' => '+380000000000',
                'website' => 'https://example.com',
                'is_active' => true,
            ],
        );

        $electricianCat = Category::query()->where('slug', Str::slug('Електрик'))->first();

        Offer::query()->firstOrCreate(
            ['business_profile_id' => $provider->id, 'title' => 'Послуги електрика (демо)'],
            [
                'category_id' => $electricianCat?->id,
                'type' => 'service',
                'description' => 'Діагностика, заміна розеток, монтаж освітлення.',
                'price_from' => 300,
                'price_to' => null,
                'currency' => 'UAH',
                'is_active' => true,
            ],
        );

        PortfolioPost::query()->firstOrCreate(
            ['business_profile_id' => $provider->id, 'title' => 'Заміна проводки'],
            [
                'body' => 'Фото/опис роботи (MVP: без медіа, лише текст).',
                'published_at' => now(),
            ],
        );

        Story::query()->firstOrCreate(
            ['business_profile_id' => $provider->id, 'media_path' => 'stories/demo.png'],
            [
                'caption' => 'Сьогодні вільні слоти!',
                'expires_at' => now()->addDay(),
            ],
        );

        // Review/Deal flow will be fully seeded later once deals UI exists.
        Review::query()->where('business_profile_id', $provider->id)->delete();
    }
}
