<?php

namespace App\Http\Requests;

use App\Models\TrackingEvent;
use App\Services\TrackingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackingEventRequest extends FormRequest
{
    /**
     * Cek hak akses dulu sebelum validasi,
     * jadi customer langsung dapat 403.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('addTracking', $this->route('shipment')) ?? false;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', Rule::in(TrackingEvent::TYPES)],
            'status' => [
                'nullable',
                'required_if:event_type,'.TrackingEvent::TYPE_STATUS_CHANGE,
                Rule::in(array_keys(TrackingService::TRANSITIONS)),
            ],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'required_if:event_type,'.TrackingEvent::TYPE_EXCEPTION,
            ],
        ];
    }
}