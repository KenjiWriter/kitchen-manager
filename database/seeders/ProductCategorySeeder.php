<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Owoce',
                'icon' => '🍎',
                'color' => '#EF4444',
                'order' => 1,
            ],
            [
                'name' => 'Warzywa',
                'icon' => '🥕',
                'color' => '#10B981',
                'order' => 2,
            ],
            [
                'name' => 'Mięso',
                'icon' => '🥩',
                'color' => '#DC2626',
                'order' => 3,
            ],
            [
                'name' => 'Nabiał',
                'icon' => '🥛',
                'color' => '#3B82F6',
                'order' => 4,
            ],
            [
                'name' => 'Pieczywo',
                'icon' => '🍞',
                'color' => '#D97706',
                'order' => 5,
            ],
            [
                'name' => 'Mrożonki',
                'icon' => '🧊',
                'color' => '#06B6D4',
                'order' => 6,
            ],
            [
                'name' => 'Napoje',
                'icon' => '🥤',
                'color' => '#8B5CF6',
                'order' => 7,
            ],
            [
                'name' => 'Przyprawy',
                'icon' => '🌶️',
                'color' => '#F59E0B',
                'order' => 8,
            ],
            [
                'name' => 'Słodycze',
                'icon' => '🍫',
                'color' => '#EC4899',
                'order' => 9,
            ],
            [
                'name' => 'Konserwy',
                'icon' => '🥫',
                'color' => '#6B7280',
                'order' => 10,
            ],
            [
                'name' => 'Makarony i ryż',
                'icon' => '🍝',
                'color' => '#F97316',
                'order' => 11,
            ],
            [
                'name' => 'Inne',
                'icon' => '📦',
                'color' => '#64748B',
                'order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
