<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            [
                'bank_name' => 'ABC Bank',
                'date' => '2024-01-15',
                'description' => 'Fuel purchase at Shell Station',
                'withdraw' => 150.00,
                'deposit' => null,
                'balance' => 5850.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'ABC Bank',
                'date' => '2024-01-14',
                'description' => 'Client payment received',
                'withdraw' => null,
                'deposit' => 2500.00,
                'balance' => 6000.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'ABC Bank',
                'date' => '2024-01-10',
                'description' => 'Electric bill payment',
                'withdraw' => 450.00,
                'deposit' => null,
                'balance' => 3500.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'ABC Bank',
                'date' => '2024-01-08',
                'description' => 'Restaurant - Dinner with client',
                'withdraw' => 85.50,
                'deposit' => null,
                'balance' => 3950.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'ABC Bank',
                'date' => '2024-01-05',
                'description' => 'Travel expenses - Flight booking',
                'withdraw' => 650.00,
                'deposit' => null,
                'balance' => 4035.50,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'XYZ Bank',
                'date' => '2024-01-20',
                'description' => 'Medical checkup',
                'withdraw' => 200.00,
                'deposit' => null,
                'balance' => 7800.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'XYZ Bank',
                'date' => '2024-01-18',
                'description' => 'Salary deposit',
                'withdraw' => null,
                'deposit' => 5000.00,
                'balance' => 8000.00,
                'year' => 2024,
                'month' => 1,
            ],
            [
                'bank_name' => 'XYZ Bank',
                'date' => '2024-01-12',
                'description' => 'Movie tickets',
                'withdraw' => 45.00,
                'deposit' => null,
                'balance' => 3000.00,
                'year' => 2024,
                'month' => 1,
            ],
        ];

        foreach ($transactions as $transaction) {
            \App\Models\Transaction::create($transaction);
        }
    }
}
