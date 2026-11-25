<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'status' => $this->current_status,
            'cargo_type' => $this->cargo_type,
            'weight_kg' => (float) $this->weight_kg,
            'volume_cbm' => $this->volume_cbm !== null ? (float) $this->volume_cbm : null,
            'expected_departure' => $this->expected_departure?->toIso8601String(),
            'expected_arrival' => $this->expected_arrival?->toIso8601String(),
            'estimated_arrival' => $this->estimated_arrival?->toIso8601String(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'carrier' => $this->whenLoaded('carrier', fn () => new CarrierResource($this->carrier)),
            'origin' => $this->whenLoaded('origin', fn () => new LocationResource($this->origin)),
            'destination' => $this->whenLoaded('destination', fn () => new LocationResource($this->destination)),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'weight_kg' => $item->weight_kg !== null ? (float) $item->weight_kg : null,
            ])),
        ];
    }
}