<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ExceptionService
{
    /**
     * Status sekarang => status berikutnya yang BOLEH.
     * RESOLVED adalah status akhir.
     */
    public const TRANSITIONS = [
        ShipmentException::STATUS_OPEN => [
            ShipmentException::STATUS_INVESTIGATING,
            ShipmentException::STATUS_RESOLVED,
        ],
        ShipmentException::STATUS_INVESTIGATING => [
            ShipmentException::STATUS_RESOLVED,
        ],
        ShipmentException::STATUS_RESOLVED => [],
    ];

    public function open(Shipment $shipment, array $data, User $actor): ShipmentException
    {
        $exception = new ShipmentException($data);
        $exception->shipment_id = $shipment->id;
        $exception->status = ShipmentException::STATUS_OPEN;
        $exception->detected_at = now();
        $exception->created_by = $actor->id;
        $exception->save();

        return $exception;
    }

    public function changeStatus(ShipmentException $exception, string $newStatus): ShipmentException
    {
        $allowed = self::TRANSITIONS[$exception->status] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'new_status' => "Invalid exception status transition: {$exception->status} to {$newStatus}.",
            ]);
        }

        $exception->status = $newStatus;

        if ($newStatus === ShipmentException::STATUS_RESOLVED) {
            $exception->resolved_at = now();
        }

        $exception->save();

        return $exception;
    }
}