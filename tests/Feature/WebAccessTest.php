<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class WebAccessTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        // Halaman tidak butuh file hasil "npm run build" saat dites
        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/shipments')->assertRedirect('/login');
    }

    public function test_every_role_can_open_the_dashboard(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_CUSTOMER] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/dashboard')->assertOk();
        }
    }

    public function test_customer_is_blocked_from_staff_and_admin_pages(): void
    {
        $this->actingAs($this->userWithRole(User::ROLE_CUSTOMER));

        foreach (['/shipments/create', '/carriers', '/locations', '/settings'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_operator_can_create_shipments_but_not_open_admin_pages(): void
    {
        $this->actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->get('/shipments/create')->assertOk();

        foreach (['/carriers', '/locations', '/settings'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_open_admin_pages(): void
    {
        $this->actingAs($this->userWithRole(User::ROLE_ADMIN));

        foreach (['/shipments/create', '/carriers', '/locations', '/settings'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_customer_can_only_open_their_own_shipment(): void
    {
        $mine = $this->makeShipment();
        $other = $this->makeShipment();

        $this->actingAs(User::find($mine->customer_id));

        $this->get("/shipments/{$mine->id}")->assertOk();
        $this->get("/shipments/{$other->id}")->assertForbidden();
        $this->get("/shipments/{$mine->id}/edit")->assertForbidden();
    }

    public function test_dashboard_numbers_come_from_the_database_and_respect_ownership(): void
    {
        $customer = $this->userWithRole(User::ROLE_CUSTOMER);
        $this->makeShipment(['customer_id' => $customer->id]);
        $this->makeShipment(['customer_id' => $customer->id]);
        $this->makeShipment(); // milik customer lain

        $mine = $this->actingAs($customer)->get('/dashboard');
        $this->assertSame(2, $mine->viewData('stats')['total']);

        $all = $this->actingAs($this->userWithRole(User::ROLE_ADMIN))->get('/dashboard');
        $this->assertSame(3, $all->viewData('stats')['total']);
    }

    public function test_shipment_list_can_be_filtered_by_status(): void
    {
        $this->makeShipment(['current_status' => 'CREATED']);
        $this->makeShipment(['current_status' => 'BOOKED']);

        $response = $this->actingAs($this->userWithRole(User::ROLE_OPERATOR))
            ->get('/shipments?status=BOOKED');

        $response->assertOk();
        $this->assertSame(1, $response->viewData('shipments')->total());
    }
}