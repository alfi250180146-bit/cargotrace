<?php

namespace App\Http\Requests;

use App\Services\ExceptionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExceptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $issue = $this->route('shipmentException');

        return $this->user()?->can('manageExceptions', $issue->shipment) ?? false;
    }

    public function rules(): array
    {
        return [
            'new_status' => ['required', Rule::in(array_keys(ExceptionService::TRANSITIONS))],
        ];
    }
}