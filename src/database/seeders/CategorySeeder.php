<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'INCOME:SALES', 'is_custom' => false],
            ['name' => 'EXPENSE:FUEL', 'is_custom' => false],
            ['name' => 'EXPENSE:ELECTRIC BILL', 'is_custom' => false],
            ['name' => 'EXPENSE:TRAVEL', 'is_custom' => false],
            ['name' => 'EXPENSE:HEALTHCARE', 'is_custom' => false],
            ['name' => 'EXPENSE:ENTERTAINMENT', 'is_custom' => false],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(
                ['name' => $category['name']],
                ['is_custom' => $category['is_custom']]
            );
        }
    }
}
