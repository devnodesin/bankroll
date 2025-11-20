<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Rule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;

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
        $this->category = Category::create([
            'name' => 'Test Category',
            'is_custom' => true,
        ]);
    }

    public function test_can_view_rules_index_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('rules.index'));

        $response->assertStatus(200)
            ->assertViewIs('rules.index')
            ->assertViewHas('rules')
            ->assertViewHas('categories');
    }

    public function test_can_create_rule(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.store'), [
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Rule created successfully.',
            ])
            ->assertJsonStructure([
                'rule' => [
                    'id',
                    'description_match',
                    'category_id',
                    'transaction_type',
                    'category',
                ],
            ]);

        $this->assertDatabaseHas('rules', [
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);
    }

    public function test_cannot_create_rule_without_description(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.store'), [
            'description_match' => '',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description_match']);
    }

    public function test_cannot_create_rule_without_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.store'), [
            'description_match' => 'AMAZON',
            'category_id' => null,
            'transaction_type' => 'withdraw',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_cannot_create_rule_with_invalid_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.store'), [
            'description_match' => 'AMAZON',
            'category_id' => 9999, // Non-existent category
            'transaction_type' => 'withdraw',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_cannot_create_rule_with_invalid_transaction_type(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.store'), [
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transaction_type']);
    }

    public function test_can_update_rule(): void
    {
        $this->actingAs($this->user);

        $rule = Rule::create([
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        $newCategory = Category::create([
            'name' => 'Updated Category',
            'is_custom' => true,
        ]);

        $response = $this->putJson(route('rules.update', $rule), [
            'description_match' => 'AWS',
            'category_id' => $newCategory->id,
            'transaction_type' => 'both',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rule updated successfully.',
            ]);

        $this->assertDatabaseHas('rules', [
            'id' => $rule->id,
            'description_match' => 'AWS',
            'category_id' => $newCategory->id,
            'transaction_type' => 'both',
        ]);
    }

    public function test_can_delete_rule(): void
    {
        $this->actingAs($this->user);

        $rule = Rule::create([
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        $response = $this->deleteJson(route('rules.destroy', $rule));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rule deleted successfully.',
            ]);

        $this->assertDatabaseMissing('rules', [
            'id' => $rule->id,
        ]);
    }

    public function test_can_apply_rules_to_transactions(): void
    {
        $this->actingAs($this->user);

        // Create a rule
        $rule = Rule::create([
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        // Create test transactions
        $transaction1 = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => now(),
            'description' => 'AMAZON.COM PURCHASE',
            'withdraw' => 50.00,
            'deposit' => null,
            'balance' => 950.00,
            'category_id' => null,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        $transaction2 = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => now(),
            'description' => 'SALARY DEPOSIT',
            'withdraw' => null,
            'deposit' => 1000.00,
            'balance' => 2000.00,
            'category_id' => null,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        $response = $this->postJson(route('rules.apply'), [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
            'overwrite' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'message',
                'count',
            ]);

        // Verify transaction1 was updated (matches AMAZON rule)
        $transaction1->refresh();
        $this->assertEquals($this->category->id, $transaction1->category_id);

        // Verify transaction2 was not updated (doesn't match rule)
        $transaction2->refresh();
        $this->assertNull($transaction2->category_id);
    }

    public function test_rules_apply_only_to_matching_transaction_type(): void
    {
        $this->actingAs($this->user);

        // Create a rule for withdrawals only
        $rule = Rule::create([
            'description_match' => 'TEST',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        // Create a withdrawal transaction
        $withdrawal = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => now(),
            'description' => 'TEST WITHDRAWAL',
            'withdraw' => 50.00,
            'deposit' => null,
            'balance' => 950.00,
            'category_id' => null,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        // Create a deposit transaction
        $deposit = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => now(),
            'description' => 'TEST DEPOSIT',
            'withdraw' => null,
            'deposit' => 100.00,
            'balance' => 1050.00,
            'category_id' => null,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        $this->postJson(route('rules.apply'), [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
            'overwrite' => false,
        ]);

        // Verify withdrawal was updated
        $withdrawal->refresh();
        $this->assertEquals($this->category->id, $withdrawal->category_id);

        // Verify deposit was not updated (rule is for withdrawals only)
        $deposit->refresh();
        $this->assertNull($deposit->category_id);
    }

    public function test_rules_respect_overwrite_setting(): void
    {
        $this->actingAs($this->user);

        $existingCategory = Category::create([
            'name' => 'Existing Category',
            'is_custom' => true,
        ]);

        // Create a rule
        $rule = Rule::create([
            'description_match' => 'AMAZON',
            'category_id' => $this->category->id,
            'transaction_type' => 'withdraw',
        ]);

        // Create a transaction with existing category
        $transaction = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => now(),
            'description' => 'AMAZON PURCHASE',
            'withdraw' => 50.00,
            'deposit' => null,
            'balance' => 950.00,
            'category_id' => $existingCategory->id,
            'year' => now()->year,
            'month' => now()->month,
        ]);

        // Apply rules without overwrite
        $this->postJson(route('rules.apply'), [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
            'overwrite' => false,
        ]);

        // Verify category was not changed
        $transaction->refresh();
        $this->assertEquals($existingCategory->id, $transaction->category_id);

        // Apply rules with overwrite
        $this->postJson(route('rules.apply'), [
            'bank' => 'Test Bank',
            'year' => now()->year,
            'month' => now()->month,
            'overwrite' => true,
        ]);

        // Verify category was updated
        $transaction->refresh();
        $this->assertEquals($this->category->id, $transaction->category_id);
    }

    public function test_apply_rules_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('rules.apply'), [
            // Missing all required fields
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bank', 'year', 'month', 'overwrite']);
    }

    public function test_rule_only_updates_classification_fields(): void
    {
        $this->actingAs($this->user);

        // Create a rule
        $rule = Rule::create([
            'description_match' => 'TEST',
            'category_id' => $this->category->id,
            'transaction_type' => 'both',
        ]);

        // Create a transaction
        $originalDate = now()->subDays(5);
        $originalDescription = 'TEST TRANSACTION';
        $originalWithdraw = 50.00;
        $originalBalance = 950.00;

        $transaction = Transaction::create([
            'bank_name' => 'Test Bank',
            'date' => $originalDate,
            'description' => $originalDescription,
            'withdraw' => $originalWithdraw,
            'deposit' => null,
            'balance' => $originalBalance,
            'category_id' => null,
            'year' => $originalDate->year,
            'month' => $originalDate->month,
        ]);

        // Apply rules
        $this->postJson(route('rules.apply'), [
            'bank' => 'Test Bank',
            'year' => $originalDate->year,
            'month' => $originalDate->month,
            'overwrite' => false,
        ]);

        // Verify only category_id was updated, not original bank data
        $transaction->refresh();
        $this->assertEquals($this->category->id, $transaction->category_id);
        $this->assertEquals($originalDate->toDateString(), $transaction->date->toDateString());
        $this->assertEquals($originalDescription, $transaction->description);
        $this->assertEquals($originalWithdraw, $transaction->withdraw);
        $this->assertEquals($originalBalance, $transaction->balance);
    }
}
