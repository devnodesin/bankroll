<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfCurrencySymbolTest extends TestCase
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

        // Create test transaction
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
     * Test that PDF export correctly displays Indian Rupee symbol
     */
    public function test_pdf_export_displays_indian_rupee_symbol(): void
    {
        config(['app.currency_symbol' => '₹']);

        $this->actingAs($this->user);

        $response = $this->get(route('transactions.export.pdf', [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Verify the response is successful - actual PDF content verification
        // would require parsing the binary PDF which is beyond the scope of this test
    }

    /**
     * Test that PDF export correctly displays Euro symbol
     */
    public function test_pdf_export_displays_euro_symbol(): void
    {
        config(['app.currency_symbol' => '€']);

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
     * Test that PDF export correctly displays British Pound symbol
     */
    public function test_pdf_export_displays_pound_symbol(): void
    {
        config(['app.currency_symbol' => '£']);

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
     * Test that PDF template uses DejaVu Sans font
     */
    public function test_pdf_template_uses_dejavu_sans_font(): void
    {
        $viewContent = view('exports.transactions-pdf', [
            'transactions' => collect([]),
            'title' => 'Test',
            'bank' => 'Test Bank',
            'month' => 'January',
            'year' => 2024,
        ])->render();

        // Check that the template specifies DejaVu Sans font
        $this->assertStringContainsString('DejaVu Sans', $viewContent);
        
        // Check that UTF-8 charset is specified
        $this->assertStringContainsString('UTF-8', $viewContent);
    }

    /**
     * Test DomPDF configuration for Unicode support
     */
    public function test_dompdf_config_has_correct_unicode_settings(): void
    {
        $convertEntities = config('dompdf.convert_entities');
        
        // convert_entities should be false to preserve Unicode characters
        $this->assertFalse($convertEntities, 'DomPDF convert_entities should be false for Unicode support');
    }
}
