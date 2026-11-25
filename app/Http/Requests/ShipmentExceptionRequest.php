<?php

namespace App\Http\Requests;

use App\Models\ShipmentException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageExceptions', $this->route('shipment')) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ShipmentException::TYPES)],
            'severity' => ['required', Rule::in(ShipmentException::SEVERITIES)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}