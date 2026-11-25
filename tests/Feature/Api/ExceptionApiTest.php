<?php

namespace Tests\Feature\Api;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class ExceptionApiTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    private function makeIssue(Shipment $shipment, string $status = 'OPEN'): ShipmentException
    {
        $issue = new ShipmentException();
        $issue->forceFill([
            'shipment_id' => $shipment->id,
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'status' => $status,
            'detected_at' => now(),
            'created_by' => $this->userWithRole(User::ROLE_OPERATOR)->id,
        ])->save();

        return $issue;
    }

    public function test_operator_can_report_an_exception(): void
    {
        $shipment = $this->makeShipment();
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson("/api/shipments/{$shipment->id}/exceptions", [
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'notes' => 'Documents under review',
        ])->assertCreated()->assertJsonPath('data.status', 'OPEN');

        $this->assertDatabaseHas('shipment_exceptions', ['shipment_id' => $shipment->id, 'type' => 'CUSTOMS_HOLD']);
    }

    public function test_unknown_exception_type_is_rejected(): void
    {
        $shipment = $this->makeShipment();
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson("/api/shipments/{$shipment->id}/exceptions", ['type' => 'ALIEN_ATTACK', 'severity' => 'HIGH'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_exception_follows_open_investigating_resolved(): void
    {
        $issue = $this->makeIssue($this->makeShipment());
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->patchJson("/api/exceptions/{$issue->id}", ['new_status' => 'INVESTIGATING'])->assertOk();

        $this->patchJson("/api/exceptions/{$issue->id}", ['new_status' => 'RESOLVED'])->assertOk();
        $this->assertNotNull($issue->fresh()->resolved_at);

        // RESOLVED adalah status akhir
        $this->patchJson("/api/exceptions/{$issue->id}", ['new_status' => 'INVESTIGATING'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['new_status']);
    }

    public function test_customer_cannot_manage_exceptions(): void
    {
        $shipment = $this->makeShipment();
        $issue = $this->makeIssue($shipment);
        Sanctum::actingAs(User::find($shipment->customer_id));

        $this->postJson("/api/shipments/{$shipment->id}/exceptions", ['type' => 'DAMAGED', 'severity' => 'LOW'])
            ->assertForbidden();

        $this->patchJson("/api/exceptions/{$issue->id}", ['new_status' => 'RESOLVED'])->assertForbidden();
    }
}