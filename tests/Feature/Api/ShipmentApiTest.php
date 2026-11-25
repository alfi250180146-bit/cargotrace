<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class ShipmentApiTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    public function test_operator_can_create_a_shipment_with_automatic_number(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $response = $this->postJson('/api/shipments', $this->shipmentPayload())
            ->assertCreated()
            ->assertJsonPath('data.status', 'CREATED');

        $this->assertMatchesRegularExpression('/^CT-\d{4}-\d{4}$/', $response->json('data.shipment_number'));
        $this->assertDatabaseHas('shipments', ['cargo_type' => 'Test cargo', 'current_status' => 'CREATED']);
    }

    public function test_arrival_before_departure_is_rejected(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $payload = $this->shipmentPayload([
            'expected_departure' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);

        $this->postJson('/api/shipments', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['expected_arrival']);
    }

    public function test_origin_and_destination_must_differ(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $payload = $this->shipmentPayload();
        $payload['destination_id'] = $payload['origin_id'];

        $this->postJson('/api/shipments', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['destination_id']);
    }

    public function test_weight_must_be_positive(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson('/api/shipments', $this->shipmentPayload(['weight_kg' => 0]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['weight_kg']);
    }

    public function test_customer_cannot_create_a_shipment(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_CUSTOMER));

        $this->postJson('/api/shipments', $this->shipmentPayload())->assertForbidden();
    }

    public function test_customer_only_sees_their_own_shipments(): void
    {
        $mine = $this->makeShipment();
        $this->makeShipment(); // milik customer lain

        Sanctum::actingAs(User::find($mine->customer_id));

        $this->getJson('/api/shipments')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_cannot_view_another_customers_shipment(): void
    {
        $someoneElses = $this->makeShipment();

        Sanctum::actingAs($this->userWithRole(User::ROLE_CUSTOMER));

        $this->getJson("/api/shipments/{$someoneElses->id}")->assertForbidden();
    }

    public function test_unknown_shipment_returns_404(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->getJson('/api/shipments/999999')
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_show_includes_eta_analysis(): void
    {
        $shipment = $this->makeShipment();

        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->getJson("/api/shipments/{$shipment->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'shipment_number'], 'eta' => ['is_delayed', 'delay_hours', 'severity', 'reason']]);
    }

    public function test_status_cannot_be_changed_through_update(): void
    {
        $shipment = $this->makeShipment();

        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $payload = $this->shipmentPayload([
            'customer_id' => $shipment->customer_id,
            'cargo_type' => 'Updated cargo',
            'current_status' => 'DELIVERED', // percobaan menimpa status lewat input
        ]);

        $this->putJson("/api/shipments/{$shipment->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.cargo_type', 'Updated cargo');

        $this->assertSame('CREATED', $shipment->fresh()->current_status);
    }

    public function test_only_admin_can_delete_a_shipment(): void
    {
        $shipment = $this->makeShipment();

        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));
        $this->deleteJson("/api/shipments/{$shipment->id}")->assertForbidden();
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);

        Sanctum::actingAs($this->userWithRole(User::ROLE_ADMIN));
        $this->deleteJson("/api/shipments/{$shipment->id}")->assertNoContent();
        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }
}