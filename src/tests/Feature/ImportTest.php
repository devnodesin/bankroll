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
}
