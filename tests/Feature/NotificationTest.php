<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_status_change_notifies_the_shipment_customer(): void
    {
        $shipment = $this->makeShipment();
        $customer = User::find($shipment->customer_id);
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson("/api/shipments/{$shipment->id}/tracking", [
            'event_type' => 'STATUS_CHANGE',
            'status' => 'BOOKED',
            'occurred_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
        ])->assertCreated();

        $this->assertCount(1, $customer->unreadNotifications);
        $this->assertSame('status', $customer->unreadNotifications->first()->data['kind']);
    }

    public function test_reporting_an_exception_notifies_the_customer(): void
    {
        $shipment = $this->makeShipment();
        $customer = User::find($shipment->customer_id);
        Sanctum::actingAs($this->userWithRole(User::ROLE_OPERATOR));

        $this->postJson("/api/shipments/{$shipment->id}/exceptions", [
            'type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
        ])->assertCreated();

        $this->assertCount(1, $customer->unreadNotifications);
        $this->assertSame('exception', $customer->unreadNotifications->first()->data['kind']);
    }

    public function test_a_later_eta_notifies_the_customer_about_the_delay(): void
    {
        $shipment = $this->makeShipment();
        $customer = User::find($shipment->customer_id);

        $shipment->estimated_arrival = $shipment->expected_arrival->copy()->addHours(5);
        $shipment->save();

        $this->assertCount(1, $customer->unreadNotifications);
        $this->assertSame('delay', $customer->unreadNotifications->first()->data['kind']);
    }

    public function test_customer_sees_notifications_and_opening_one_marks_it_read(): void
    {
        $shipment = $this->makeShipment();
        $customer = User::find($shipment->customer_id);

        $shipment->current_status = 'BOOKED';
        $shipment->save();

        $notification = $customer->notifications()->firstOrFail();

        $this->actingAs($customer)->get('/notifications')->assertOk()->assertSee('is now BOOKED');

        $this->actingAs($customer)
            ->get("/notifications/{$notification->id}")
            ->assertRedirect(route('shipments.show', $shipment));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_open_someone_elses_notification(): void
    {
        $shipment = $this->makeShipment();
        $shipment->current_status = 'BOOKED';
        $shipment->save();

        $notification = User::find($shipment->customer_id)->notifications()->firstOrFail();

        $this->actingAs($this->userWithRole(User::ROLE_CUSTOMER))
            ->get("/notifications/{$notification->id}")
            ->assertNotFound();
    }

    public function test_mark_all_as_read(): void
    {
        $shipment = $this->makeShipment();
        $customer = User::find($shipment->customer_id);

        $shipment->current_status = 'BOOKED';
        $shipment->save();

        $this->assertCount(1, $customer->unreadNotifications);

        $this->actingAs($customer)->post('/notifications/read-all')->assertRedirect();

        $this->assertCount(0, $customer->fresh()->unreadNotifications);
    }
}