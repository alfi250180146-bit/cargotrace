<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExceptionSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.test')->firstOrFail();

        // Geser ETA supaya ada contoh delay LOW / MEDIUM / HIGH
        $etaOffsets = [
            'CT-2026-0001' => 30, // HIGH
            'CT-2026-0002' => 4,  // LOW
            'CT-2026-0005' => 10, // MEDIUM
        ];

        foreach ($etaOffsets as $number => $hours) {
            $shipment = Shipment::where('shipment_number', $number)->first();
            if (! $shipment) {
                continue;
            }
            $shipment->estimated_arrival = $shipment->expected_arrival->copy()->addHours($hours);
            $shipment->save();
        }

        // [nomor shipment, jenis, severity, status, catatan]
        $rows = [
            ['CT-2026-0001', 'CUSTOMS_HOLD',        'HIGH',   'OPEN',          'Documents under customs review'],
            ['CT-2026-0005', 'WEATHER_DELAY',       'MEDIUM', 'INVESTIGATING', 'Storm on the planned route'],
            ['CT-2026-0003', 'DOCUMENTATION_ISSUE', 'LOW',    'RESOLVED',      'Missing invoice, resolved by carrier'],
        ];

        foreach ($rows as [$number, $type, $severity, $status, $notes]) {
            $shipment = Shipment::where('shipment_number', $number)->first();

            if (! $shipment || $shipment->exceptions()->exists()) {
                continue;
            }

            $issue = new ShipmentException([
                'type' => $type,
                'severity' => $severity,
                'notes' => $notes,
            ]);
            $issue->shipment_id = $shipment->id;
            $issue->status = $status;
            $issue->detected_at = now()->subHours(8);
            $issue->resolved_at = $status === ShipmentException::STATUS_RESOLVED ? now()->subHours(2) : null;
            $issue->created_by = $operator->id;
            $issue->save();
        }
    }
}