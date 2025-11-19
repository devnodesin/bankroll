<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionUpdateBugTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test category
        $this->category = Category::create(['name' => 'Test Category', 'is_custom' => true]);

        // Create a test transaction with both category and notes
        \DB::table('transactions')->insert([
            'bank_name' => 'Test Bank',
            'date' => now()->format('Y-m-d'),
            'description' => 'Original Description',
            'withdraw' => 100.00,
            'deposit' => null,
            'balance' => 900.00,
            'category_id' => $this->category->id,
            'notes' => 'Original notes',
            'year' => now()->year,
            'month' => now()->month,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->transaction = Transaction::first();
    }

    public function test_updating_category_should_not_clear_notes(): void
    {
        $this->actingAs($this->user);

        // Create another category
        $newCategory = Category::create(['name' => 'New Category', 'is_custom' => true]);

        // Update only the category_id
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'category_id' => $newCategory->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify category was updated but notes remain unchanged
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'category_id' => $newCategory->id,
            'notes' => 'Original notes', // BUG: This will be null!
        ]);
    }

    public function test_updating_notes_should_not_clear_category(): void
    {
        $this->actingAs($this->user);

        // Update only the notes
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'notes' => 'Updated notes',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify notes were updated but category remains unchanged
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'category_id' => $this->category->id, // BUG: This will be null!
            'notes' => 'Updated notes',
        ]);
    }
}
