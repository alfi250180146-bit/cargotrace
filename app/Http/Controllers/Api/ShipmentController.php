<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExceptionStatusRequest;
use App\Http\Requests\ShipmentExceptionRequest;
use App\Http\Requests\ShipmentRequest;
use App\Http\Requests\TrackingEventRequest;
use App\Http\Resources\ShipmentExceptionResource;
use App\Http\Resources\ShipmentResource;
use App\Http\Resources\TrackingEventResource;
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
        private ExceptionService $exceptions,
        private EtaService $eta,
    ) {
    }

    private const RELATIONS = ['customer', 'carrier', 'origin', 'destination', 'items'];

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipment::class);

        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(TrackingService::TRANSITIONS))],
            'carrier_id' => ['nullable', 'integer'],
            'delay' => ['nullable', Rule::in(['delayed', 'on_time'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $shipments = Shipment::with(['customer', 'carrier', 'origin', 'destination'])
            // Customer hanya boleh melihat shipment miliknya sendiri
            ->when(
                ! $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR),
                fn ($query) => $query->where('customer_id', $user->id)
            )
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where('shipment_number', 'like', '%'.$value.'%'))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('current_status', $value))
            ->when($filters['carrier_id'] ?? null, fn ($query, $value) => $query->where('carrier_id', $value))
            ->when(($filters['delay'] ?? null) === 'delayed', fn ($query) => $query->whereColumn('estimated_arrival', '>', 'expected_arrival'))
            ->when(($filters['delay'] ?? null) === 'on_time', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('estimated_arrival')->orWhereColumn('estimated_arrival', '<=', 'expected_arrival');
            }))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return ShipmentResource::collection($shipments);
    }

    public function store(ShipmentRequest $request)
    {
        Gate::authorize('create', Shipment::class);

        $shipment = $this->shipments->create($request->validated(), $request->user());
        $shipment->load(self::RELATIONS);

        return (new ShipmentResource($shipment))->response()->setStatusCode(201);
    }

    public function show(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load(self::RELATIONS);
        $analysis = $this->eta->analyze($shipment);

        return (new ShipmentResource($shipment))->additional([
            'eta' => [
                'is_delayed' => $analysis['is_delayed'],
                'delay_hours' => $analysis['delay_hours'],
                'severity' => $analysis['severity'],
                'threshold_hours' => $analysis['threshold_hours'],
                'over_threshold' => $analysis['over_threshold'],
                'reason' => $analysis['reason'],
            ],
        ]);
    }

    public function update(ShipmentRequest $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $this->shipments->update($shipment, $request->validated());
        $shipment->load(self::RELATIONS);

        return new ShipmentResource($shipment);
    }

    public function destroy(Shipment $shipment)
    {
        Gate::authorize('delete', $shipment);

        $shipment->delete();

        return response()->noContent();
    }

    public function tracking(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        return TrackingEventResource::collection(
            $shipment->trackingEvents()->with(['location', 'creator'])->get()
        );
    }

    public function addTracking(TrackingEventRequest $request, Shipment $shipment)
    {
        $event = $this->tracking->record($shipment, $request->validated(), $request->user());
        $event->load(['location', 'creator']);

        return (new TrackingEventResource($event))->response()->setStatusCode(201);
    }

    public function exceptions(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        return ShipmentExceptionResource::collection($shipment->exceptions);
    }

    public function addException(ShipmentExceptionRequest $request, Shipment $shipment)
    {
        $exception = $this->exceptions->open($shipment, $request->validated(), $request->user());

        return (new ShipmentExceptionResource($exception))->response()->setStatusCode(201);
    }

    public function updateException(ExceptionStatusRequest $request, ShipmentException $shipmentException)
    {
        $exception = $this->exceptions->changeStatus($shipmentException, $request->validated()['new_status']);

        return new ShipmentExceptionResource($exception);
    }
}