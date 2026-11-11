<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipment::class);

        $user = $request->user();

        $shipments = Shipment::with(['customer', 'carrier', 'origin', 'destination'])
            // Selain Admin/Operator, hanya tampilkan shipment milik user sendiri
            ->when(
                ! $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR),
                fn ($query) => $query->where('customer_id', $user->id)
            )
            ->latest('id')
            ->paginate(10);

        return view('shipments.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load(['customer', 'carrier', 'origin', 'destination', 'items']);

        return view('shipments.show', compact('shipment'));
    }
}