<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Buat shipment baru. Nomor, status awal, dan pembuat diisi di sini,
     * bukan dari input form.
     */
    public function create(array $data, User $actor): Shipment
    {
        return DB::transaction(function () use ($data, $actor) {
            $shipment = new Shipment($data);
            $shipment->shipment_number = $this->generateNumber();
            $shipment->current_status = Shipment::STATUS_CREATED;
            $shipment->created_by = $actor->id;

            // Kalau ETA tidak diisi, anggap sama dengan expected arrival
            if (empty($data['estimated_arrival'])) {
                $shipment->estimated_arrival = $data['expected_arrival'];
            }

            $shipment->save();

            return $shipment;
        });
    }

    public function update(Shipment $shipment, array $data): Shipment
    {
        // Status tidak ikut diubah di sini (tidak ada di $fillable)
        $shipment->update($data);

        return $shipment;
    }

    /**
     * Format: CT-TAHUN-NNNN, contoh CT-2026-0007.
     * Kolom shipment_number bersifat unique, jadi duplikat tetap ditolak database.
     */
    public function generateNumber(): string
    {
        $prefix = 'CT-'.now()->format('Y').'-';

        $last = Shipment::where('shipment_number', 'like', $prefix.'%')
            ->orderByDesc('shipment_number')
            ->value('shipment_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}