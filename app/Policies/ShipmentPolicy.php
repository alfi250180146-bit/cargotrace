<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Semua user yang login boleh membuka daftar shipment
     * (isi daftarnya difilter di controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin & Operator melihat semua shipment.
     * Customer hanya shipment miliknya.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR)) {
            return true;
        }

        return $shipment->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR);
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR);
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function addTracking(User $user, Shipment $shipment): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR);
    }

    public function manageExceptions(User $user, Shipment $shipment): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR);
    }
}