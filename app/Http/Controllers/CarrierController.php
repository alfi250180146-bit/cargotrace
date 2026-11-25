<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarrierController extends Controller
{
    public function index()
    {
        return view('carriers.index', [
            'carriers' => Carrier::withCount('shipments')->orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('carriers.form', ['carrier' => new Carrier()]);
    }

    public function store(Request $request)
    {
        Carrier::create($this->validated($request));

        return redirect()->route('carriers.index')->with('status', 'Carrier created.');
    }

    public function edit(Carrier $carrier)
    {
        return view('carriers.form', compact('carrier'));
    }

    public function update(Request $request, Carrier $carrier)
    {
        $carrier->update($this->validated($request, $carrier));

        return redirect()->route('carriers.index')->with('status', 'Carrier updated.');
    }

    public function destroy(Carrier $carrier)
    {
        if ($carrier->shipments()->exists()) {
            return back()->withErrors(['carrier' => 'This carrier still has shipments and cannot be deleted.']);
        }

        $carrier->delete();

        return redirect()->route('carriers.index')->with('status', 'Carrier deleted.');
    }

    private function validated(Request $request, ?Carrier $carrier = null): array
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('carriers', 'code')->ignore($carrier?->id)],
            'mode' => ['required', Rule::in(['sea', 'air', 'land'])],
            'delay_threshold_hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
    }
}