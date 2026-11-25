<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'status' => $this->status,
            'is_exception' => $this->is_exception,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'description' => $this->description,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'location' => $this->whenLoaded('location', fn () => $this->location
                ? new LocationResource($this->location)
                : null),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator->name),
        ];
    }
}