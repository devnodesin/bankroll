<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CreditDebitImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test bank
        Bank::create(['name' => 'Test Bank']);
    }

    public function test_can_preview_credit_debit_format_file(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Amount,Type,Balance\n";
        $csvContent .= "15/03/2024,ATM Withdrawal,500.00,DR,4500.00\n";
        $csvContent .= "16/03/2024,Salary Credit,5000.00,CR,9500.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.preview'), [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json();
        $this->assertArrayHasKey('parser_type', $data);
        $this->assertArrayHasKey('available_parsers', $data);
        
        // Should detect credit-debit parser
        $this->assertEquals('credit-debit', $data['parser_type']);
    }

    public function test_can_import_credit_debit_format_file(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Amount,Type,Balance\n";
        $csvContent .= "15/03/2024,ATM Withdrawal,500.00,DR,4500.00\n";
        $csvContent .= "16/03/2024,Salary Credit,5000.00,CR,9500.00\n";
        $csvContent .= "17/03/2024,Online Purchase,100.00,DEBIT,9400.00\n";
        $csvContent .= "18/03/2024,Refund,50.00,CREDIT,9450.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
            'column_mappings' => $columnMappings,
            'parser_type' => 'credit-debit',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(4, Transaction::count());
        
        // Check debit transaction (mapped to withdraw)
        $this->assertDatabaseHas('transactions', [
            'bank_name' => 'Test Bank',
            'description' => 'ATM Withdrawal',
            'withdraw' => 500.00,
            'deposit' => null,
            'balance' => 4500.00,
        ]);
        
        // Check credit transaction (mapped to deposit)
        $this->assertDatabaseHas('transactions', [
            'bank_name' => 'Test Bank',
            'description' => 'Salary Credit',
            'withdraw' => null,
            'deposit' => 5000.00,
            'balance' => 9500.00,
        ]);

        // Check DEBIT variant
        $this->assertDatabaseHas('transactions', [
            'description' => 'Online Purchase',
            'withdraw' => 100.00,
            'deposit' => null,
        ]);

        // Check CREDIT variant
        $this->assertDatabaseHas('transactions', [
            'description' => 'Refund',
            'withdraw' => null,
            'deposit' => 50.00,
        ]);
    }

    public function test_credit_debit_import_validates_required_mappings(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Col1,Col2,Col3,Col4,Col5\n";
        $csvContent .= "15/03/2024,Test,100.00,CR,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        // Missing type mapping
        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'balance' => 4,
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
            'column_mappings' => $columnMappings,
            'parser_type' => 'credit-debit',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $data = $response->json();
        $this->assertStringContainsString('type', $data['message']);
        
        $this->assertEquals(0, Transaction::count());
    }

    public function test_credit_debit_import_rejects_invalid_type_indicator(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Amount,Type,Balance\n";
        $csvContent .= "15/03/2024,Test Transaction,100.00,INVALID,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
            'column_mappings' => $columnMappings,
            'parser_type' => 'credit-debit',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $data = $response->json();
        $this->assertStringContainsString('Type field must be CR/Credit', $data['errors'][0]);
        
        $this->assertEquals(0, Transaction::count());
    }

    public function test_credit_debit_format_supports_different_date_formats(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Amount,Type,Balance\n";
        $csvContent .= "2024-03-15,Test DR,100.00,DR,900.00\n";
        $csvContent .= "2024-03-16,Test CR,50.00,CR,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'type' => 3,
            'balance' => 4,
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'Y-m-d',
            'file' => $file,
            'column_mappings' => $columnMappings,
            'parser_type' => 'credit-debit',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
        
        $transaction = Transaction::first();
        $this->assertEquals('2024-03-15', $transaction->date->format('Y-m-d'));
    }

    public function test_auto_detects_credit_debit_format_mappings(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Transaction Date,Particulars,Transaction Amount,CR/DR,Closing Balance\n";
        $csvContent .= "15/03/2024,Test,100.00,CR,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.preview'), [
            'file' => $file
        ]);

        $data = $response->json();
        $mappings = $data['mappings'];

        // Should detect all CR/DR format columns
        $this->assertEquals(0, $mappings['date']);
        $this->assertEquals(1, $mappings['description']);
        $this->assertEquals(2, $mappings['amount']);
        $this->assertEquals(3, $mappings['type']);
        $this->assertEquals(4, $mappings['balance']);
    }
}
