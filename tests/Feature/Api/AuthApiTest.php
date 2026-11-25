<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_a_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_protected_endpoint_requires_a_token(): void
    {
        $this->getJson('/api/shipments')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_register_validates_input(): void
    {
        $this->postJson('/api/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_always_creates_a_customer(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'password' => 'password123',
            'role' => 'admin', // percobaan menaikkan hak akses
        ])->assertCreated();

        $this->assertSame('customer', User::where('email', 'mallory@example.test')->value('role'));
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}