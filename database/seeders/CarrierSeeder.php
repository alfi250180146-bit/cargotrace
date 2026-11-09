<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            ['NOL', 'Nusantara Ocean Lines',  'sea',  null],
            ['PBS', 'Pacific Bridge Shipping', 'sea',  18],
            ['EAF', 'Euro Atlantic Freight',   'sea',  null],
            ['SKG', 'SkyCargo Global',         'air',  6],
            ['TCR', 'TransContinental Road',   'land', 12],
        ];

        foreach ($carriers as [$code, $name, $mode, $threshold]) {
            Carrier::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'mode' => $mode, 'delay_threshold_hours' => $threshold]
            );
        }
    }
}