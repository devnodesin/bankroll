<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTest extends TestCase
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

    public function test_import_requires_authentication(): void
    {
        $file = UploadedFile::fake()->create('transactions.csv', 100);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'file' => $file
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_import_requires_bank_name(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('transactions.csv', 100);

        $response = $this->post(route('transactions.import'), [
            'file' => $file
        ]);

        $response->assertSessionHasErrors('bank_name');
    }

    public function test_import_requires_file(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank'
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_validates_file_type(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'file' => $file
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_validates_file_size(): void
    {
        $this->actingAs($this->user);

        // Create a file larger than 5MB
        $file = UploadedFile::fake()->create('transactions.csv', 6000);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'file' => $file
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_can_import_valid_csv_file(): void
    {
        $this->actingAs($this->user);

        // Create a valid CSV content
        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "15/03/2024,Test Transaction 1,100.00,,900.00\n";
        $csvContent .= "16/03/2024,Test Transaction 2,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file
        ]);

        // Import controller returns JSON response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify transactions were created
        $this->assertEquals(2, Transaction::count());
        
        $this->assertDatabaseHas('transactions', [
            'bank_name' => 'Test Bank',
            'description' => 'Test Transaction 1',
            'withdraw' => 100.00,
            'deposit' => null,
        ]);
    }

    public function test_can_preview_file(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Transaction Date,Details,Debit,Credit,Closing Balance\n";
        $csvContent .= "15/03/2024,Test Transaction,100.00,,900.00\n";
        $csvContent .= "16/03/2024,Another Transaction,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.preview'), [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json();
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('preview', $data);
        $this->assertArrayHasKey('mappings', $data);
        $this->assertCount(5, $data['headers']);
        $this->assertCount(2, $data['preview']);
    }

    public function test_auto_detects_column_mappings(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Transaction Date,Particulars,Debit,Credit,Balance\n";
        $csvContent .= "15/03/2024,Test,100.00,,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.preview'), [
            'file' => $file
        ]);

        $data = $response->json();
        $mappings = $data['mappings'];

        // Should auto-detect based on column names
        $this->assertEquals(0, $mappings['date']); // Transaction Date
        $this->assertEquals(1, $mappings['description']); // Particulars
        $this->assertEquals(2, $mappings['withdraw']); // Debit
        $this->assertEquals(3, $mappings['deposit']); // Credit
        $this->assertEquals(4, $mappings['balance']); // Balance
    }

    public function test_can_import_with_custom_column_mappings(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Txn Date,Details,Debit Amt,Credit Amt,Closing Bal\n";
        $csvContent .= "15/03/2024,Purchase at Store,200.00,,800.00\n";
        $csvContent .= "16/03/2024,Salary,,3000.00,3800.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            'withdraw' => 2,
            'deposit' => 3,
            'balance' => 4,
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
            'column_mappings' => $columnMappings,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
        
        $this->assertDatabaseHas('transactions', [
            'bank_name' => 'Test Bank',
            'description' => 'Purchase at Store',
            'withdraw' => 200.00,
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'bank_name' => 'Test Bank',
            'description' => 'Salary',
            'deposit' => 3000.00,
        ]);
    }

    public function test_import_validates_required_column_mappings(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Col1,Col2,Col3\n";
        $csvContent .= "15/03/2024,Test,100.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        // Missing required mappings
        $columnMappings = json_encode([
            'date' => 0,
            'description' => 1,
            // Missing balance
        ]);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
            'column_mappings' => $columnMappings,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(0, Transaction::count());
    }

    public function test_import_without_mappings_requires_exact_columns(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Txn Date,Details,Amt\n";
        $csvContent .= "15/03/2024,Test,100.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y',
            'file' => $file,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'needs_mapping' => true,
            ]);

        $this->assertEquals(0, Transaction::count());
    }

    public function test_can_import_with_dd_mm_yy_format(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "15/03/24,Test Transaction 1,100.00,,900.00\n";
        $csvContent .= "16/03/24,Test Transaction 2,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/y',
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
        
        $transaction = Transaction::first();
        $this->assertEquals('2024-03-15', $transaction->date->format('Y-m-d'));
    }

    public function test_can_import_with_mm_dd_yyyy_format(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "03/15/2024,Test Transaction 1,100.00,,900.00\n";
        $csvContent .= "03/16/2024,Test Transaction 2,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'm/d/Y',
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
        
        $transaction = Transaction::first();
        $this->assertEquals('2024-03-15', $transaction->date->format('Y-m-d'));
    }

    public function test_can_import_with_yyyy_mm_dd_format(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "2024-03-15,Test Transaction 1,100.00,,900.00\n";
        $csvContent .= "2024-03-16,Test Transaction 2,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'Y-m-d',
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
        
        $transaction = Transaction::first();
        $this->assertEquals('2024-03-15', $transaction->date->format('Y-m-d'));
    }

    public function test_import_rejects_wrong_date_format(): void
    {
        $this->actingAs($this->user);

        // File has DD/MM/YYYY dates but user selects MM/DD/YYYY format
        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "15/03/2024,Test Transaction,100.00,,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'm/d/Y', // Wrong format
            'file' => $file
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $data = $response->json();
        $this->assertStringContainsString('not in the expected format', $data['errors'][0]);
        $this->assertStringContainsString('MM/DD/YYYY', $data['errors'][0]);
        
        $this->assertEquals(0, Transaction::count());
    }

    public function test_import_validates_date_format_parameter(): void
    {
        $this->actingAs($this->user);

        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "15/03/2024,Test,100.00,,900.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'invalid-format',
            'file' => $file
        ]);

        $response->assertSessionHasErrors('date_format');
    }

    public function test_import_accepts_dates_with_different_separators(): void
    {
        $this->actingAs($this->user);

        // File has dates with hyphens instead of slashes
        $csvContent = "Date,Description,Withdraw,Deposit,Balance\n";
        $csvContent .= "15-03-2024,Test Transaction 1,100.00,,900.00\n";
        $csvContent .= "16.03.2024,Test Transaction 2,,50.00,950.00\n";

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        $response = $this->post(route('transactions.import'), [
            'bank_name' => 'Test Bank',
            'date_format' => 'd/m/Y', // Format uses /, but file uses - and .
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(2, Transaction::count());
    }
}
