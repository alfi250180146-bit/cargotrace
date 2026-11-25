<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrierResource;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarrierController extends Controller
{
    public function index()
    {
        return CarrierResource::collection(Carrier::orderBy('name')->paginate(50));
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('carriers', 'code')],
            'mode' => ['required', Rule::in(['sea', 'air', 'land'])],
            'delay_threshold_hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $carrier = Carrier::create($data);

        return (new CarrierResource($carrier))->response()->setStatusCode(201);
    }
}