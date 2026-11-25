<?php

namespace Tests\Concerns;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Str;

trait BuildsFixtures
{
    protected function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /**
     * Carrier dan dua lokasi dasar untuk shipment uji.
     */
    protected function masterData(): array
    {
        $carrier = Carrier::firstOrCreate(
            ['code' => 'TST'],
            ['name' => 'Test Carrier', 'mode' => 'sea']
        );

        $origin = Location::firstOrCreate(
            ['code' => 'AAA'],
            ['name' => 'Origin Port', 'country' => 'Aland', 'latitude' => 1.5, 'longitude' => 2.5, 'type' => 'seaport']
        );

        $destination = Location::firstOrCreate(
            ['code' => 'BBB'],
            ['name' => 'Destination Port', 'country' => 'Bland', 'latitude' => 3.5, 'longitude' => 4.5, 'type' => 'seaport']
        );

        return [$carrier, $origin, $destination];
    }

    /**
     * Buat shipment langsung ke database (melewati validasi, untuk menyiapkan kondisi uji).
     */
    protected function makeShipment(array $attributes = []): Shipment
    {
        [$carrier, $origin, $destination] = $this->masterData();

        $shipment = new Shipment();
        $shipment->forceFill(array_merge([
            'shipment_number' => 'CT-T-'.Str::upper(Str::random(8)),
            'customer_id' => $this->userWithRole(User::ROLE_CUSTOMER)->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Test cargo',
            'weight_kg' => 1000,
            'volume_cbm' => 5,
            'expected_departure' => now()->subDays(3),
            'expected_arrival' => now()->addDays(10),
            'estimated_arrival' => now()->addDays(10),
            'current_status' => Shipment::STATUS_CREATED,
            'created_by' => $this->userWithRole(User::ROLE_OPERATOR)->id,
        ], $attributes))->save();

        return $shipment;
    }

    /**
     * Isi request body yang valid untuk membuat shipment lewat API.
     */
    protected function shipmentPayload(array $overrides = []): array
    {
        [$carrier, $origin, $destination] = $this->masterData();

        return array_merge([
            'customer_id' => $this->userWithRole(User::ROLE_CUSTOMER)->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Test cargo',
            'weight_kg' => 1000,
            'volume_cbm' => 5,
            'expected_departure' => now()->addDay()->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(14)->format('Y-m-d H:i:s'),
        ], $overrides);
    }
}