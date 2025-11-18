<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
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

        // Create a test transaction (using DB insert to bypass guarded fields)
        \DB::table('transactions')->insert([
            'bank_name' => 'Test Bank',
            'date' => now()->format('Y-m-d'),
            'description' => 'Original Description',
            'withdraw' => 100.00,
            'deposit' => null,
            'balance' => 900.00,
            'year' => now()->year,
            'month' => now()->month,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->transaction = Transaction::first();
    }

    public function test_can_update_transaction_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_can_update_transaction_notes(): void
    {
        $this->actingAs($this->user);

        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'notes' => 'Test notes for this transaction',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'notes' => 'Test notes for this transaction',
        ]);
    }

    public function test_cannot_modify_original_bank_data_via_mass_assignment(): void
    {
        $this->actingAs($this->user);

        // Attempt to change protected fields (they should be ignored)
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'description' => 'Modified Description',
            'withdraw' => 500.00,
            'balance' => 500.00,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(200);

        // Verify original data remains unchanged
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'description' => 'Original Description',
            'withdraw' => 100.00,
            'balance' => 900.00,
            'category_id' => $this->category->id, // Only this should be updated
        ]);
    }

    public function test_can_clear_transaction_category(): void
    {
        $this->actingAs($this->user);

        // First set a category
        $this->transaction->update(['category_id' => $this->category->id]);

        // Then clear it
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'category_id' => null,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'category_id' => null,
        ]);
    }

    public function test_can_clear_transaction_notes(): void
    {
        $this->actingAs($this->user);

        // First set notes
        $this->transaction->update(['notes' => 'Some notes']);

        // Then clear it
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'notes' => null,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'notes' => null,
        ]);
    }

    public function test_transaction_model_protects_original_fields(): void
    {
        $transaction = Transaction::first();
        
        // Try to update protected fields using model
        $transaction->fill([
            'description' => 'Hacked Description',
            'withdraw' => 999.99,
            'balance' => 1.00,
        ]);
        $transaction->save();

        // Verify they weren't changed
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Original Description',
            'withdraw' => 100.00,
            'balance' => 900.00,
        ]);
    }

    public function test_can_load_transactions_with_filters(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('transactions.get'), [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'transactions' => [
                    '*' => ['id', 'date', 'description', 'withdraw', 'deposit', 'balance']
                ]
            ]);
    }

    public function test_transaction_update_requires_authentication(): void
    {
        $response = $this->patchJson(route('transactions.update', $this->transaction), [
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(401);
    }
}
