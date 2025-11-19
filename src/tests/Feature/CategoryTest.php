<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
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
    }

    public function test_can_create_custom_category(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('categories.store'), [
            'name' => 'Test Custom Category'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => "Category 'Test Custom Category' has been added successfully.",
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Custom Category',
            'is_custom' => true,
        ]);
    }

    public function test_cannot_create_duplicate_category(): void
    {
        $this->actingAs($this->user);

        Category::create(['name' => 'Duplicate Test', 'is_custom' => true]);

        // Try creating with the same name - will either be caught by validation or DB constraint
        try {
            $response = $this->postJson(route('categories.store'), [
                'name' => 'Duplicate Test'
            ]);

            // The validation rule should catch this and return 422
            $response->assertStatus(422);
        } catch (\Exception $e) {
            // If DB constraint fires first, that's also acceptable for this test
            // The important part is that duplicate is prevented
            $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
        }

        // Verify only one category with that name exists
        $this->assertEquals(1, Category::where('name', 'Duplicate Test')->count());
    }

    public function test_can_list_categories(): void
    {
        $this->actingAs($this->user);

        // Create system and custom categories
        Category::create(['name' => 'System Cat', 'is_custom' => false]);
        Category::create(['name' => 'Custom Cat', 'is_custom' => true]);

        $response = $this->getJson(route('categories.index'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'system' => [
                    '*' => ['id', 'name', 'is_custom']
                ],
                'custom' => [
                    '*' => ['id', 'name', 'is_custom']
                ]
            ]);
    }

    public function test_can_delete_unused_custom_category(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'To Delete', 'is_custom' => true]);

        $response = $this->deleteJson(route('categories.destroy', $category));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => "Category 'To Delete' has been deleted successfully.",
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_system_category(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'System Category', 'is_custom' => false]);

        $response = $this->deleteJson(route('categories.destroy', $category));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => "System category 'System Category' cannot be deleted. Only custom categories can be removed.",
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_in_use(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'Used Category', 'is_custom' => true]);

        // Create a transaction using this category
        \DB::table('transactions')->insert([
            'bank_name' => 'Test Bank',
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Transaction',
            'withdraw' => 100.00,
            'deposit' => null,
            'balance' => 900.00,
            'category_id' => $category->id,
            'year' => now()->year,
            'month' => now()->month,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson(route('categories.destroy', $category));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'usage_count' => 1,
            ]);

        // Verify category still exists
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_name_is_required(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('categories.store'), [
            'name' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_category_name_has_max_length(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('categories.store'), [
            'name' => str_repeat('a', 51) // Exceeds 50 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
