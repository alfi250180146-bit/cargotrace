<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentRequest extends FormRequest
{
    /**
     * Otorisasi (siapa yang boleh) dicek di controller lewat Policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // customer harus user dengan role customer
            'customer_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_CUSTOMER),
            ],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'volume_cbm' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'estimated_arrival' => ['nullable', 'date', 'after_or_equal:expected_departure'],
        ];
    }
}