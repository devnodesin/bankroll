<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('name', 'testuser')->first();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_cannot_access_home(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_home(): void
    {
        $user = User::where('name', 'testuser')->first();
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_login_requires_username(): void
    {
        $response = $this->post(route('login'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['username']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'testuser',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
