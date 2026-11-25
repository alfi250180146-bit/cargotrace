<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\EtaService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EtaService::DEFAULTS as $key => $value) {
            Setting::setValue($key, $value);
        }
    }
}