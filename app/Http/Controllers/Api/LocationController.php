<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        return LocationResource::collection(Location::orderBy('country')->orderBy('name')->paginate(50));
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('locations', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'type' => ['required', Rule::in(['seaport', 'airport', 'warehouse'])],
        ]);

        $location = Location::create($data);

        return (new LocationResource($location))->response()->setStatusCode(201);
    }
}