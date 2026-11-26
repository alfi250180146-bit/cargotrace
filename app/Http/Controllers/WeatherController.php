<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Gate;

class WeatherController extends Controller
{
    public function show(Shipment $shipment, WeatherService $weather)
    {
        // Aturan akses sama dengan halaman detail: customer hanya boleh shipment miliknya
        Gate::authorize('view', $shipment);

        $shipment->load(['origin', 'trackingEvents.location']);

        // Titik terakhir dari event normal yang punya koordinat; kalau tidak ada, pakai origin
        $event = $shipment->trackingEvents
            ->where('is_exception', false)
            ->filter(fn ($e) => $e->latitude !== null && $e->longitude !== null)
            ->last();

        if ($event) {
            $latitude = (float) $event->latitude;
            $longitude = (float) $event->longitude;
            $place = $event->location?->name ?? 'Last tracking point';
        } else {
            $latitude = (float) $shipment->origin->latitude;
            $longitude = (float) $shipment->origin->longitude;
            $place = $shipment->origin->name.' (origin)';
        }

        return response()->json(['place' => $place] + $weather->current($latitude, $longitude));
    }
}