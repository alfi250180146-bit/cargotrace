<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    public function __construct(private ShipmentService $shipments)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipment::class);

        $user = $request->user();

        $shipments = Shipment::with(['customer', 'carrier', 'origin', 'destination'])
            ->when(
                ! $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR),
                fn ($query) => $query->where('customer_id', $user->id)
            )
            ->latest('id')
            ->paginate(10);

        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        Gate::authorize('create', Shipment::class);

        return view('shipments.create', ['shipment' => new Shipment()] + $this->formData());
    }

    public function store(ShipmentRequest $request)
    {
        Gate::authorize('create', Shipment::class);

        $shipment = $this->shipments->create($request->validated(), $request->user());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Shipment '.$shipment->shipment_number.' created.');
    }

    public function show(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load(['customer', 'carrier', 'origin', 'destination', 'items']);

        return view('shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        return view('shipments.edit', compact('shipment') + $this->formData());
    }

    public function update(ShipmentRequest $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $this->shipments->update($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('status', 'Shipment updated.');
    }

    public function destroy(Shipment $shipment)
    {
        Gate::authorize('delete', $shipment);

        $number = $shipment->shipment_number;
        $shipment->delete();

        return redirect()
            ->route('shipments.index')
            ->with('status', 'Shipment '.$number.' deleted.');
    }

    /**
     * Data untuk dropdown di form.
     */
    private function formData(): array
    {
        return [
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(),
            'carriers' => Carrier::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ];
    }
}