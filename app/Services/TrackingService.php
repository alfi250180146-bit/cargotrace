<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackingService
{
    /**
     * State machine: status sekarang => status berikutnya yang BOLEH.
     * DELIVERED adalah status akhir (tidak punya status berikutnya).
     */
    public const TRANSITIONS = [
        Shipment::STATUS_CREATED    => [Shipment::STATUS_BOOKED],
        Shipment::STATUS_BOOKED     => [Shipment::STATUS_PICKED_UP],
        Shipment::STATUS_PICKED_UP  => [Shipment::STATUS_IN_TRANSIT],
        Shipment::STATUS_IN_TRANSIT => [Shipment::STATUS_DELIVERED],
        Shipment::STATUS_DELIVERED  => [],
    ];

    public function nextStatuses(string $current): array
    {
        return self::TRANSITIONS[$current] ?? [];
    }

    /**
     * Catat satu tracking event. Semua aturan bisnis diperiksa di sini.
     * Dibungkus transaksi + lock supaya dua operator tidak bisa
     * mengubah status shipment yang sama pada saat bersamaan.
     */
    public function record(Shipment $shipment, array $data, User $actor): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();

            $current = $shipment->current_status;
            $type = $data['event_type'];
            $occurredAt = Carbon::parse($data['occurred_at']);

            // Aturan 1: status akhir tidak boleh menerima event lagi
            if ($this->nextStatuses($current) === []) {
                throw ValidationException::withMessages([
                    'event_type' => "Shipment is already {$current}. No more events can be added.",
                ]);
            }

            // Aturan 2: urutan waktu tidak boleh mundur dari event terakhir
            $latest = TrackingEvent::where('shipment_id', $shipment->id)->max('occurred_at');
            if ($latest && $occurredAt->lt(Carbon::parse($latest))) {
                throw ValidationException::withMessages([
                    'occurred_at' => 'Event time cannot be earlier than the latest event ('
                        .Carbon::parse($latest)->format('d M Y H:i').').',
                ]);
            }

            $newStatus = $current;

            // Aturan 3: perubahan status harus mengikuti state machine
            if ($type === TrackingEvent::TYPE_STATUS_CHANGE) {
                $newStatus = $data['status'];
                $allowed = $this->nextStatuses($current);

                if (! in_array($newStatus, $allowed, true)) {
                    throw ValidationException::withMessages([
                        'status' => "Invalid status transition: {$current} to {$newStatus}. "
                            .'Allowed next status: '.implode(', ', $allowed).'.',
                    ]);
                }
            }

            // Aturan 4: checkpoint hanya masuk akal saat barang sudah diambil
            if ($type === TrackingEvent::TYPE_CHECKPOINT
                && ! in_array($current, [Shipment::STATUS_PICKED_UP, Shipment::STATUS_IN_TRANSIT], true)) {
                throw ValidationException::withMessages([
                    'event_type' => 'Checkpoint events are only allowed while the shipment is PICKED_UP or IN_TRANSIT.',
                ]);
            }

            // Koordinat diambil dari lokasi yang dipilih kalau tidak diisi manual
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            if (! empty($data['location_id']) && ($latitude === null || $longitude === null)) {
                $location = Location::find($data['location_id']);
                $latitude = $latitude ?? $location?->latitude;
                $longitude = $longitude ?? $location?->longitude;
            }

            $event = new TrackingEvent([
                'event_type'  => $type,
                'status'      => $newStatus,
                'occurred_at' => $occurredAt,
                'location_id' => $data['location_id'] ?? null,
                'latitude'    => $latitude,
                'longitude'   => $longitude,
                'description' => $data['description'] ?? null,
            ]);
            $event->shipment_id = $shipment->id;
            $event->is_exception = $type === TrackingEvent::TYPE_EXCEPTION;
            $event->created_by = $actor->id;
            $event->save();

            if ($newStatus !== $current) {
                $shipment->current_status = $newStatus;
                $shipment->save();
            }

            return $event;
        });
    }
}