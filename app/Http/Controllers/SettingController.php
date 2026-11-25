<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\EtaService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private EtaService $eta)
    {
    }

    public function edit()
    {
        $values = [];
        foreach (array_keys(EtaService::DEFAULTS) as $key) {
            $values[$key] = $this->eta->setting($key);
        }

        return view('settings.edit', compact('values'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'delay_threshold_hours' => ['required', 'numeric', 'min:0', 'max:10000'],
            'severity_low_max_hours' => ['required', 'numeric', 'min:0', 'max:10000'],
            'severity_medium_max_hours' => ['required', 'numeric', 'gt:severity_low_max_hours', 'max:10000'],
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->route('settings.edit')->with('status', 'Settings saved.');
    }
}