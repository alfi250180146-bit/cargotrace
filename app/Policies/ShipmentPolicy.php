<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Semua user yang login boleh membuka daftar shipment
     * (isi daftarnya sendiri difilter di controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin & Operator boleh melihat semua shipment.
     * Customer hanya boleh melihat shipment miliknya.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR)) {
            return true;
        }

        return $shipment->customer_id === $user->id;
    }
}