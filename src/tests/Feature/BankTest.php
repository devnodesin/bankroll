<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTest extends TestCase
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

    public function test_can_create_bank(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('banks.store'), [
            'name' => 'Test Bank'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => "Bank 'Test Bank' has been added successfully.",
            ]);

        $this->assertDatabaseHas('banks', [
            'name' => 'Test Bank',
        ]);
    }

    public function test_cannot_create_duplicate_bank(): void
    {
        $this->actingAs($this->user);

        Bank::create(['name' => 'Duplicate Bank']);

        $response = $this->postJson(route('banks.store'), [
            'name' => 'Duplicate Bank'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_list_banks(): void
    {
        $this->actingAs($this->user);

        Bank::create(['name' => 'Bank A']);
        Bank::create(['name' => 'Bank B']);

        $response = $this->getJson(route('banks.index'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'banks' => [
                    '*' => ['id', 'name']
                ]
            ]);

        $banks = $response->json('banks');
        $this->assertEquals(2, count($banks));
    }

    public function test_can_delete_bank(): void
    {
        $this->actingAs($this->user);

        $bank = Bank::create(['name' => 'To Delete']);

        $response = $this->deleteJson(route('banks.destroy', $bank));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => "Bank 'To Delete' has been deleted successfully.",
            ]);

        $this->assertDatabaseMissing('banks', [
            'id' => $bank->id,
        ]);
    }

    public function test_bank_name_is_required(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('banks.store'), [
            'name' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_bank_name_has_max_length(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('banks.store'), [
            'name' => str_repeat('a', 101) // Exceeds 100 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_bank_operations_require_authentication(): void
    {
        $response = $this->postJson(route('banks.store'), [
            'name' => 'Test Bank'
        ]);

        $response->assertStatus(401);
    }
}
