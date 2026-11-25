<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExceptionStatusRequest;
use App\Http\Requests\ShipmentExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;

class ShipmentExceptionController extends Controller
{
    public function __construct(private ExceptionService $exceptions)
    {
    }

    public function store(ShipmentExceptionRequest $request, Shipment $shipment)
    {
        $this->exceptions->open($shipment, $request->validated(), $request->user());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Exception recorded.');
    }

    public function update(ExceptionStatusRequest $request, ShipmentException $shipmentException)
    {
        $this->exceptions->changeStatus($shipmentException, $request->validated()['new_status']);

        return redirect()
            ->route('shipments.show', $shipmentException->shipment_id)
            ->with('status', 'Exception status updated.');
    }
}