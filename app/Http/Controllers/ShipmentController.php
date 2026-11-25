<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\EtaService;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    public function __construct(
        private ShipmentService $shipments,
        private TrackingService $tracking,
        private EtaService $eta,
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipment::class);

        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(TrackingService::TRANSITIONS))],
            'carrier_id' => ['nullable', 'integer'],
            'origin_id' => ['nullable', 'integer'],
            'destination_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'delay' => ['nullable', Rule::in(['delayed', 'on_time'])],
            'sort' => ['nullable', Rule::in(['id', 'shipment_number', 'expected_arrival', 'current_status'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $sort = $filters['sort'] ?? 'id';
        $dir = $filters['dir'] ?? 'desc';

        $shipments = Shipment::with(['customer', 'carrier', 'origin', 'destination'])
            // Customer hanya boleh melihat shipment miliknya sendiri
            ->when(
                ! $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR),
                fn ($query) => $query->where('customer_id', $user->id)
            )
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where('shipment_number', 'like', '%'.$value.'%'))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('current_status', $value))
            ->when($filters['carrier_id'] ?? null, fn ($query, $value) => $query->where('carrier_id', $value))
            ->when($filters['origin_id'] ?? null, fn ($query, $value) => $query->where('origin_id', $value))
            ->when($filters['destination_id'] ?? null, fn ($query, $value) => $query->where('destination_id', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('expected_arrival', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('expected_arrival', '<=', $value))
            ->when(($filters['delay'] ?? null) === 'delayed', fn ($query) => $query->whereColumn('estimated_arrival', '>', 'expected_arrival'))
            ->when(($filters['delay'] ?? null) === 'on_time', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('estimated_arrival')->orWhereColumn('estimated_arrival', '<=', 'expected_arrival');
            }))
            ->orderBy($sort, $dir)
            ->when($sort !== 'id', fn ($query) => $query->orderByDesc('id'))
            ->paginate(10)
            ->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'sort' => $sort,
            'dir' => $dir,
            'carriers' => Carrier::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'statuses' => array_keys(TrackingService::TRANSITIONS),
        ]);
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

        $shipment->load([
            'customer', 'carrier', 'origin', 'destination', 'items',
            'trackingEvents.location', 'trackingEvents.creator',
            'exceptions',
        ]);

        return view('shipments.show', [
            'shipment' => $shipment,
            'locations' => Location::orderBy('name')->get(),
            'allStatuses' => array_keys(TrackingService::TRANSITIONS),
            'nextStatuses' => $this->tracking->nextStatuses($shipment->current_status),
            'eta' => $this->eta->analyze($shipment),
            'exceptionTypes' => ShipmentException::TYPES,
            'severities' => ShipmentException::SEVERITIES,
            'exceptionTransitions' => ExceptionService::TRANSITIONS,
        ]);
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