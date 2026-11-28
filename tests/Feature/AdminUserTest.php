<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function asAdmin(): User
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_only_admin_can_open_user_management(): void
    {
        foreach ([User::ROLE_OPERATOR, User::ROLE_CUSTOMER] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/users')->assertForbidden();
        }

        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))->get('/users')->assertOk();
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $this->asAdmin();

        $this->post('/users', [
            'name' => 'New Operator',
            'email' => 'new.operator@example.test',
            'role' => 'operator',
            'password' => 'password123',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'new.operator@example.test')->firstOrFail();

        $this->assertSame('operator', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_invalid_role_and_duplicate_email_are_rejected(): void
    {
        $admin = $this->asAdmin();

        $this->post('/users', [
            'name' => 'Bad Role',
            'email' => 'bad.role@example.test',
            'role' => 'superuser',
            'password' => 'password123',
        ])->assertSessionHasErrors('role');

        $this->post('/users', [
            'name' => 'Duplicate',
            'email' => $admin->email,
            'role' => 'customer',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_short_password_is_rejected(): void
    {
        $this->asAdmin();

        $this->post('/users', [
            'name' => 'Weak',
            'email' => 'weak@example.test',
            'role' => 'customer',
            'password' => 'short',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_can_change_another_users_role(): void
    {
        $this->asAdmin();
        $user = $this->userWithRole(User::ROLE_CUSTOMER);

        $this->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'operator',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('operator', $user->fresh()->role);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->asAdmin();

        $this->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'customer',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->asAdmin();

        $this->delete("/users/{$admin->id}")->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_with_shipments_cannot_be_deleted(): void
    {
        $shipment = $this->makeShipment();
        $this->asAdmin();

        $this->delete("/users/{$shipment->customer_id}")->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $shipment->customer_id]);
    }

    public function test_unused_user_can_be_deleted(): void
    {
        $this->asAdmin();
        $user = $this->userWithRole(User::ROLE_CUSTOMER);

        $this->delete("/users/{$user->id}")->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}