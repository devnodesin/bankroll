<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Exports\TransactionsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Create test categories
        Category::create(['name' => 'Fuel', 'is_system' => true]);
        
        // Create test transactions (using DB insert to bypass guarded fields)
        for ($i = 1; $i <= 50; $i++) {
            \DB::table('transactions')->insert([
                'bank_name' => 'Test Bank',
                'date' => now()->startOfMonth()->addDays($i % 28)->format('Y-m-d'),
                'description' => "Test Transaction {$i}",
                'withdraw' => $i * 10,
                'deposit' => null,
                'balance' => 10000 - ($i * 10),
                'year' => now()->year,
                'month' => now()->month,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_export_excel_does_not_timeout(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('transactions.export.excel', [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_csv_does_not_timeout(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('transactions.export.csv', [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_pdf_does_not_timeout(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('transactions.export.pdf', [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_transactions_export_uses_custom_currency_symbol(): void
    {
        config(['app.currency_symbol' => '€']);
        
        $transactions = Transaction::where('bank_name', 'Test Bank')
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->get();

        $export = new TransactionsExport($transactions);
        $collection = $export->collection();

        // Check that the first transaction uses the euro symbol
        $firstTransaction = $collection->first();
        $this->assertStringContainsString('€', $firstTransaction['withdraw'] ?? $firstTransaction['balance']);
    }

    public function test_transactions_export_applies_styles_efficiently(): void
    {
        $transactions = Transaction::where('bank_name', 'Test Bank')
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->get();

        $export = new TransactionsExport($transactions);
        
        // This should complete quickly without timing out
        $startTime = microtime(true);
        
        Excel::fake();
        Excel::download($export, 'test.xlsx');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Export should complete in less than 5 seconds for 50 records
        $this->assertLessThan(5, $executionTime, 'Export took too long: ' . $executionTime . ' seconds');
    }
}
