<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;

class TrackingEventController extends Controller
{
    public function __construct(private TrackingService $tracking)
    {
    }

    public function store(TrackingEventRequest $request, Shipment $shipment)
    {
        $this->tracking->record($shipment, $request->validated(), $request->user());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Tracking event added.');
    }
}