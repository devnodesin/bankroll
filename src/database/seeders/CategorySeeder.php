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
            ['name' => 'EXPENSES:PURCHASE:MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSES:PURCHASE:RAW_MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSES:PURCHASE:TRADING_GOODS', 'is_custom' => false],
            ['name' => 'EXPENSES:OFFICE:RENT', 'is_custom' => false],
            ['name' => 'EXPENSES:OFFICE:SUPPLIES', 'is_custom' => false],
            ['name' => 'EXPENSES:OFFICE:TRAVEL', 'is_custom' => false],
            ['name' => 'EXPENSES:OFFICE:MEALS_AND_ENTERTAINMENT', 'is_custom' => false],
            ['name' => 'EXPENSES:OFFICE:REPAIRS_AND_MAINTENANCE', 'is_custom' => false],
            ['name' => 'EXPENSES:VEHICLE:FUEL', 'is_custom' => false],
            ['name' => 'EXPENSES:VEHICLE:MAINTENANCE', 'is_custom' => false],
            ['name' => 'EXPENSES:BILLS:ELECTRICITY', 'is_custom' => false],
            ['name' => 'EXPENSES:BILLS:CLOUD_HOSTING', 'is_custom' => false],
            ['name' => 'EXPENSES:BILLS:INTERNET_AND_PHONE', 'is_custom' => false],
            ['name' => 'EXPENSES:BILLS:SUBSCRIPTION', 'is_custom' => false],
            ['name' => 'EXPENSES:FEES:BANK_CHARGES', 'is_custom' => false],
            ['name' => 'EXPENSES:FEES:PROFESSIONAL', 'is_custom' => false],
            ['name' => 'EXPENSES:FEES:AUDIT', 'is_custom' => false],
            ['name' => 'EXPENSES:TAX:GST', 'is_custom' => false],
            ['name' => 'EXPENSES:TAX:TDS', 'is_custom' => false],
            ['name' => 'EXPENSES:TAX:OTHER', 'is_custom' => false],
            ['name' => 'EXPENSES:FREIGHT_SHIPPING_LOGISTICS', 'is_custom' => false],
            ['name' => 'EXPENSES:PACKING_MATERIALS', 'is_custom' => false],
            ['name' => 'EXPENSES:SALARY', 'is_custom' => false],
            ['name' => 'EXPENSES:CONTRACT_LABOR', 'is_custom' => false],
            ['name' => 'EXPENSES:MARKETING_AND_ADS', 'is_custom' => false],
            ['name' => 'EXPENSES:OTHER', 'is_custom' => false],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(
                ['name' => $category['name']],
                ['is_custom' => $category['is_custom']]
            );
        }
    }
}
