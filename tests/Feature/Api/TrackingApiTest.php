<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class TrackingApiTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    private function event(array $overrides = []): array
    {
        return array_merge([
            'event_type' => 'STATUS_CHANGE',
            'status' => 'BOOKED',
            'occurred_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
        ], $overrides);
    }

    private function asOperator(): void
    {
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));
    }

    public function test_valid_status_transition_updates_the_shipment(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event())
            ->assertCreated()
            ->assertJsonPath('data.status', 'BOOKED');

        $this->assertSame('BOOKED', $shipment->fresh()->current_status);
    }

    public function test_skipping_a_status_is_rejected(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event(['status' => 'DELIVERED']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertSame('CREATED', $shipment->fresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 0);
    }

    public function test_going_backwards_is_rejected(): void
    {
        $shipment = $this->makeShipment(['current_status' => 'BOOKED']);
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event(['status' => 'CREATED']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_delivered_shipment_accepts_no_more_events(): void
    {
        $shipment = $this->makeShipment(['current_status' => 'DELIVERED']);
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    public function test_event_time_cannot_be_earlier_than_the_latest_event(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'status' => 'BOOKED',
            'occurred_at' => now()->subHour()->format('Y-m-d H:i:s'),
        ]))->assertCreated();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'status' => 'PICKED_UP',
            'occurred_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ]))->assertUnprocessable()->assertJsonValidationErrors(['occurred_at']);

        $this->assertSame('BOOKED', $shipment->fresh()->current_status);
    }

    public function test_event_time_cannot_be_in_the_future(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'occurred_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]))->assertUnprocessable()->assertJsonValidationErrors(['occurred_at']);
    }

    public function test_exception_event_requires_a_description(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'event_type' => 'EXCEPTION',
            'status' => null,
        ]))->assertUnprocessable()->assertJsonValidationErrors(['description']);
    }

    public function test_exception_event_does_not_change_the_status(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'event_type' => 'EXCEPTION',
            'status' => null,
            'description' => 'Customs hold',
        ]))->assertCreated();

        $this->assertSame('CREATED', $shipment->fresh()->current_status);
        $this->assertDatabaseHas('tracking_events', ['shipment_id' => $shipment->id, 'is_exception' => 1]);
    }

    public function test_checkpoint_is_only_allowed_after_pickup(): void
    {
        $booked = $this->makeShipment(['current_status' => 'BOOKED']);
        $inTransit = $this->makeShipment(['current_status' => 'IN_TRANSIT']);
        $this->asOperator();

        $checkpoint = $this->event(['event_type' => 'CHECKPOINT', 'status' => null]);

        $this->postJson("/api/shipments/{$booked->id}/tracking", $checkpoint)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);

        $this->postJson("/api/shipments/{$inTransit->id}/tracking", $checkpoint)->assertCreated();
        $this->assertSame('IN_TRANSIT', $inTransit->fresh()->current_status);
    }

    public function test_customer_cannot_add_events_but_can_read_their_timeline(): void
    {
        $shipment = $this->makeShipment();
        Sanctum::actingAs(User::find($shipment->customer_id));

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event())->assertForbidden();
        $this->getJson("/api/shipments/{$shipment->id}/tracking")->assertOk();
    }

    public function test_history_is_kept_when_new_events_are_added(): void
    {
        $shipment = $this->makeShipment();
        $this->asOperator();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'status' => 'BOOKED',
            'occurred_at' => now()->subHours(3)->format('Y-m-d H:i:s'),
        ]))->assertCreated();

        $this->postJson("/api/shipments/{$shipment->id}/tracking", $this->event([
            'status' => 'PICKED_UP',
            'occurred_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ]))->assertCreated();

        $this->getJson("/api/shipments/{$shipment->id}/tracking")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'BOOKED')
            ->assertJsonPath('data.1.status', 'PICKED_UP');
    }
}