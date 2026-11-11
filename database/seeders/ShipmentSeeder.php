<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.test')->firstOrFail();

        // [nomor, email customer, carrier, asal, tujuan, kargo, berat kg, status, hari berangkat, hari tiba]
        $rows = [
            ['CT-2026-0001', 'customer@cargotrace.test',  'NOL', 'IDJKT', 'NLRTM', 'Palm oil (drums)',    18000, 'IN_TRANSIT', -10,  18],
            ['CT-2026-0002', 'customer@cargotrace.test',  'PBS', 'CNSHA', 'USLAX', 'Electronics',           9500, 'PICKED_UP',   -2,  20],
            ['CT-2026-0003', 'customer@cargotrace.test',  'SKG', 'SGSIN', 'AUSYD', 'Medical equipment',      850, 'DELIVERED', -12,  -9],
            ['CT-2026-0004', 'customer@cargotrace.test',  'EAF', 'AEJEA', 'DEHAM', 'Machinery parts',      14200, 'BOOKED',       3,  25],
            ['CT-2026-0005', 'customer2@cargotrace.test', 'NOL', 'BRSSZ', 'ZADUR', 'Coffee (bags)',        21000, 'IN_TRANSIT',  -6,  12],
            ['CT-2026-0006', 'customer2@cargotrace.test', 'PBS', 'USLAX', 'CNSHA', 'Agricultural goods',   12800, 'CREATED',      7,  30],
        ];

        foreach ($rows as [$number, $email, $carrierCode, $from, $to, $cargo, $weight, $status, $depDays, $arrDays]) {
            $customer = User::where('email', $email)->firstOrFail();

            $shipment = Shipment::firstOrNew(['shipment_number' => $number]);
            $shipment->fill([
                'customer_id'        => $customer->id,
                'carrier_id'         => Carrier::where('code', $carrierCode)->firstOrFail()->id,
                'origin_id'          => Location::where('code', $from)->firstOrFail()->id,
                'destination_id'     => Location::where('code', $to)->firstOrFail()->id,
                'cargo_type'         => $cargo,
                'weight_kg'          => $weight,
                'volume_cbm'         => round($weight / 400, 2),
                'expected_departure' => now()->addDays($depDays)->startOfHour(),
                'expected_arrival'   => now()->addDays($arrDays)->startOfHour(),
                'estimated_arrival'  => now()->addDays($arrDays)->startOfHour(),
            ]);

            // Kolom ini tidak ada di $fillable, jadi diisi langsung (bukan dari input user)
            $shipment->current_status = $status;
            $shipment->created_by = $operator->id;
            $shipment->save();

            $shipment->items()->firstOrCreate(
                ['description' => $cargo],
                ['quantity' => 1, 'weight_kg' => $weight]
            );
        }
    }
}