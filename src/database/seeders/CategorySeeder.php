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
            ['name' => 'INCOME:SALES:ONLINE', 'is_custom' => false],
            ['name' => 'INCOME:SALES:CASH', 'is_custom' => false],
            ['name' => 'INCOME:OTHER', 'is_custom' => false],
            ['name' => 'EXPENSE:PURCHASE:MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSE:PURCHASE:RAW_MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSE:PURCHASE:TRADING_GOODS', 'is_custom' => false],
            ['name' => 'EXPENSE:OFFICE:RENT', 'is_custom' => false],
            ['name' => 'EXPENSE:OFFICE:SUPPLIES', 'is_custom' => false],
            ['name' => 'EXPENSE:OFFICE:TRAVEL', 'is_custom' => false],
            ['name' => 'EXPENSE:OFFICE:MEALS_AND_ENTERTAINMENT', 'is_custom' => false],
            ['name' => 'EXPENSE:OFFICE:REPAIRS_AND_MAINTENANCE', 'is_custom' => false],
            ['name' => 'EXPENSE:VEHICLE:FUEL', 'is_custom' => false],
            ['name' => 'EXPENSE:VEHICLE:MAINTENANCE', 'is_custom' => false],
            ['name' => 'EXPENSE:BILLS:ELECTRICITY', 'is_custom' => false],
            ['name' => 'EXPENSE:BILLS:CLOUD_HOSTING', 'is_custom' => false],
            ['name' => 'EXPENSE:BILLS:INTERNET_AND_PHONE', 'is_custom' => false],
            ['name' => 'EXPENSE:BILLS:SUBSCRIPTION', 'is_custom' => false],
            ['name' => 'EXPENSE:FEES:BANK_CHARGES', 'is_custom' => false],
            ['name' => 'EXPENSE:FEES:PROFESSIONAL', 'is_custom' => false],
            ['name' => 'EXPENSE:FEES:AUDIT', 'is_custom' => false],
            ['name' => 'EXPENSE:TAX:GST', 'is_custom' => false],
            ['name' => 'EXPENSE:TAX:TDS', 'is_custom' => false],
            ['name' => 'EXPENSE:TAX:OTHER', 'is_custom' => false],
            ['name' => 'EXPENSE:FREIGHT_SHIPPING_LOGISTICS', 'is_custom' => false],
            ['name' => 'EXPENSE:PACKING_MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSE:SALARY', 'is_custom' => false],
            ['name' => 'EXPENSE:CONTRACT_LABOR', 'is_custom' => false],
            ['name' => 'EXPENSE:MARKETING_AND_ADS', 'is_custom' => false],
            ['name' => 'EXPENSE:OTHER', 'is_custom' => false],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(
                ['name' => $category['name']],
                ['is_custom' => $category['is_custom']]
            );
        }
    }
}
