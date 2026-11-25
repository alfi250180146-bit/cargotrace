<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        return view('locations.index', [
            'locations' => Location::orderBy('country')->orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('locations.form', ['location' => new Location()]);
    }

    public function store(Request $request)
    {
        Location::create($this->validated($request));

        return redirect()->route('locations.index')->with('status', 'Location created.');
    }

    public function edit(Location $location)
    {
        return view('locations.form', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($this->validated($request, $location));

        return redirect()->route('locations.index')->with('status', 'Location updated.');
    }

    public function destroy(Location $location)
    {
        if ($location->originShipments()->exists() || $location->destinationShipments()->exists()) {
            return back()->withErrors(['location' => 'This location is used by shipments and cannot be deleted.']);
        }

        $location->delete();

        return redirect()->route('locations.index')->with('status', 'Location deleted.');
    }

    private function validated(Request $request, ?Location $location = null): array
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        return $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('locations', 'code')->ignore($location?->id)],
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'type' => ['required', Rule::in(['seaport', 'airport', 'warehouse'])],
        ]);
    }
}