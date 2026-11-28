<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    public function test_creating_a_shipment_is_recorded_with_the_actor(): void
    {
        $payload = $this->shipmentPayload();
        $operator = $this->userWithRole(User::ROLE_OPERATOR);
        Sanctum::actingAs($operator);

        $response = $this->postJson('/api/shipments', $payload)->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $operator->id,
            'action' => 'created',
            'entity_type' => 'Shipment',
            'entity_id' => $response->json('data.id'),
        ]);
    }

    public function test_status_change_is_recorded_with_old_and_new_values(): void
    {
        $shipment = $this->makeShipment();
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson("/api/shipments/{$shipment->id}/tracking", [
            'event_type' => 'STATUS_CHANGE',
            'status' => 'BOOKED',
            'occurred_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
        ])->assertCreated();

        $log = AuditLog::where('entity_type', 'Shipment')
            ->where('entity_id', $shipment->id)
            ->where('action', 'updated')
            ->firstOrFail();

        $this->assertSame('CREATED', $log->old_values['current_status']);
        $this->assertSame('BOOKED', $log->new_values['current_status']);
    }

    public function test_changes_without_a_logged_in_user_are_not_recorded(): void
    {
        $this->makeShipment();

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_passwords_are_never_stored_in_the_audit_log(): void
    {
        $this->actingAs($this->userWithRole(User::ROLE_ADMIN));
        $this->withoutVite();

        $this->post('/users', [
            'name' => 'Audit Subject',
            'email' => 'audit.subject@example.test',
            'role' => 'customer',
            'password' => 'password123',
        ])->assertRedirect();

        $log = AuditLog::where('entity_type', 'User')->where('action', 'created')->latest('id')->firstOrFail();

        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertSame('customer', $log->new_values['role']);
    }

    public function test_only_admin_can_open_the_audit_log(): void
    {
        $this->withoutVite();

        $this->actingAs($this->userWithRole(User::ROLE_OPERATOR))->get('/audit-logs')->assertForbidden();
        $this->actingAs($this->userWithRole(User::ROLE_CUSTOMER))->get('/audit-logs')->assertForbidden();
        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))->get('/audit-logs')->assertOk();
    }
}