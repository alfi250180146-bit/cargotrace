<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TrackingEventSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.test')->firstOrFail();

        $order = [
            Shipment::STATUS_CREATED,
            Shipment::STATUS_BOOKED,
            Shipment::STATUS_PICKED_UP,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_DELIVERED,
        ];

        // Titik singgah (checkpoint) untuk shipment tertentu
        $checkpoints = ['CT-2026-0001' => 'AEJEA'];

        foreach (Shipment::with(['origin', 'destination'])->get() as $shipment) {
            if ($shipment->trackingEvents()->exists()) {
                continue; // jangan duplikat kalau seeder dijalankan ulang
            }

            $n = array_search($shipment->current_status, $order, true);

            // Rentang waktu riwayat: dari sebelum berangkat sampai event terakhir
            $end = $shipment->current_status === Shipment::STATUS_DELIVERED
                ? $shipment->expected_arrival->copy()
                : now()->subHours(6);
            $start = $shipment->expected_departure->copy()->min(now()->subDay())->subDay();
            $span = max($end->getTimestamp() - $start->getTimestamp(), 0);

            foreach (array_slice($order, 0, $n + 1) as $k => $status) {
                $at = $n === 0
                    ? now()->subHours(2)
                    : $start->copy()->addSeconds(intdiv($span * $k, $n));

                $location = $status === Shipment::STATUS_DELIVERED
                    ? $shipment->destination
                    : $shipment->origin;

                $this->makeEvent(
                    $shipment,
                    TrackingEvent::TYPE_STATUS_CHANGE,
                    $status,
                    $at,
                    $location,
                    "Status changed to {$status}",
                    $operator
                );
            }

            if (isset($checkpoints[$shipment->shipment_number])
                && $shipment->current_status === Shipment::STATUS_IN_TRANSIT) {
                $this->makeEvent(
                    $shipment,
                    TrackingEvent::TYPE_CHECKPOINT,
                    $shipment->current_status,
                    now()->subHours(3),
                    Location::where('code', $checkpoints[$shipment->shipment_number])->first(),
                    'Vessel passed transshipment hub',
                    $operator
                );
            }
        }
    }

    private function makeEvent(
        Shipment $shipment,
        string $type,
        string $status,
        Carbon $at,
        ?Location $location,
        string $description,
        User $actor
    ): void {
        $event = new TrackingEvent([
            'event_type'  => $type,
            'status'      => $status,
            'occurred_at' => $at,
            'location_id' => $location?->id,
            'latitude'    => $location?->latitude,
            'longitude'   => $location?->longitude,
            'description' => $description,
        ]);
        $event->shipment_id = $shipment->id;
        $event->is_exception = false;
        $event->created_by = $actor->id;
        $event->save();
    }
}