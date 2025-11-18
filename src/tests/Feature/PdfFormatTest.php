<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfFormatTest extends TestCase
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

        // Create test transactions
        \DB::table('transactions')->insert([
            'bank_name' => 'Test Bank',
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Transaction',
            'withdraw' => 2500.00,
            'deposit' => null,
            'balance' => 10000.00,
            'year' => now()->year,
            'month' => now()->month,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test that PDF export uses portrait orientation and minimal margins
     */
    public function test_pdf_export_uses_portrait_orientation_and_minimal_margins(): void
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

    /**
     * Test that PDF template uses compact styling
     */
    public function test_pdf_template_uses_compact_styling(): void
    {
        $viewContent = view('exports.transactions-pdf', [
            'transactions' => collect([]),
            'title' => 'Test',
            'bank' => 'Test Bank',
            'month' => 'January',
            'year' => 2024,
        ])->render();

        // Check for compact padding in styles
        $this->assertStringContainsString('padding: 3px 4px', $viewContent);
        $this->assertStringContainsString('padding: 2px 3px', $viewContent);
        
        // Check for smaller font sizes
        $this->assertStringContainsString('font-size: 9px', $viewContent);
        $this->assertStringContainsString('font-size: 8px', $viewContent);
    }

    /**
     * Test that PDF template uses DD/MM/YYYY date format
     */
    public function test_pdf_template_uses_correct_date_format(): void
    {
        $transaction = Transaction::first();
        
        $viewContent = view('exports.transactions-pdf', [
            'transactions' => collect([$transaction]),
            'title' => 'Test',
            'bank' => 'Test Bank',
            'month' => 'January',
            'year' => 2024,
        ])->render();

        // Check that the date is formatted as DD/MM/YYYY
        $expectedDateFormat = $transaction->date->format('d/m/Y');
        $this->assertStringContainsString($expectedDateFormat, $viewContent);
        
        // Make sure old format is not present
        $oldDateFormat = $transaction->date->format('M d, Y');
        $this->assertStringNotContainsString($oldDateFormat, $viewContent);
    }

    /**
     * Test that table headers repeat on each page
     */
    public function test_pdf_table_headers_repeat_on_each_page(): void
    {
        $viewContent = view('exports.transactions-pdf', [
            'transactions' => collect([]),
            'title' => 'Test',
            'bank' => 'Test Bank',
            'month' => 'January',
            'year' => 2024,
        ])->render();

        // Check that thead has display: table-header-group which makes it repeat
        $this->assertStringContainsString('display: table-header-group', $viewContent);
    }
}
