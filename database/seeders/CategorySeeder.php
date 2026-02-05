<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // MVP стартовий набір категорій. Потім — винести в адмінку.
        $roots = [
            'Ремонт та будівництво' => [
                'Електрик',
                'Сантехнік',
                'Штукатурка',
                'Плитка',
                'Малярні роботи',
            ],
            'Товари та магазини' => [
                'Булочна',
                'Кавʼярня',
                'Магазин',
            ],
        ];

        foreach ($roots as $rootName => $children) {
            $root = Category::query()->firstOrCreate(
                ['slug' => Str::slug($rootName)],
                ['name' => $rootName, 'parent_id' => null],
            );

            foreach ($children as $childName) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $root->id],
                );
            }
        }
    }
}
