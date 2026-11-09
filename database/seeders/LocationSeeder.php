<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['IDJKT', 'Tanjung Priok, Jakarta', 'Indonesia',    -6.1040,  106.8806, 'seaport'],
            ['SGSIN', 'Port of Singapore',      'Singapore',     1.2644,  103.8200, 'seaport'],
            ['CNSHA', 'Shanghai Port',          'China',        31.2304,  121.4737, 'seaport'],
            ['NLRTM', 'Port of Rotterdam',      'Netherlands',  51.9490,    4.1425, 'seaport'],
            ['DEHAM', 'Port of Hamburg',        'Germany',      53.5461,    9.9661, 'seaport'],
            ['USLAX', 'Port of Los Angeles',    'United States', 33.7292, -118.2620, 'seaport'],
            ['AEJEA', 'Jebel Ali, Dubai',       'UAE',          25.0118,   55.0618, 'seaport'],
            ['AUSYD', 'Port Botany, Sydney',    'Australia',   -33.9729,  151.2174, 'seaport'],
            ['BRSSZ', 'Port of Santos',         'Brazil',      -23.9608,  -46.3336, 'seaport'],
            ['ZADUR', 'Port of Durban',         'South Africa', -29.8587,   31.0218, 'seaport'],
        ];

        foreach ($locations as [$code, $name, $country, $lat, $lng, $type]) {
            Location::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'country' => $country, 'latitude' => $lat, 'longitude' => $lng, 'type' => $type]
            );
        }
    }
}