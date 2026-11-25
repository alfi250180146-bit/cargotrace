<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;

class DashboardService
{
    public function __construct(private EtaService $eta)
    {
    }

    public function build(User $user): array
    {
        $isStaff = $user->hasRole(User::ROLE_ADMIN, User::ROLE_OPERATOR);

        // Customer hanya melihat angka dari shipment miliknya
        $exceptionQuery = fn () => ShipmentException::query()->when(
            ! $isStaff,
            fn ($q) => $q->whereHas('shipment', fn ($s) => $s->where('customer_id', $user->id))
        );

        $shipments = Shipment::query()
            ->when(! $isStaff, fn ($q) => $q->where('customer_id', $user->id))
            ->with('carrier')
            ->get();

        $total = $shipments->count();

        $statusLabels = array_keys(TrackingService::TRANSITIONS);
        $statusValues = array_map(
            fn ($status) => $shipments->where('current_status', $status)->count(),
            $statusLabels
        );

        $delayedNow = 0;
        $lateCount = 0;
        $delivered = 0;
        $deliveredOnTime = 0;
        $delaySum = 0.0;

        foreach ($shipments as $shipment) {
            $analysis = $this->eta->analyze($shipment);

            if ($analysis['is_delayed']) {
                $lateCount++;
                $delaySum += $analysis['delay_hours'];
            }

            if ($shipment->current_status === Shipment::STATUS_DELIVERED) {
                $delivered++;
                if (! $analysis['is_delayed']) {
                    $deliveredOnTime++;
                }
            } elseif ($analysis['is_delayed']) {
                $delayedNow++;
            }
        }

        $withException = $exceptionQuery()->distinct()->count('shipment_id');
        $openExceptions = $exceptionQuery()->where('status', '!=', ShipmentException::STATUS_RESOLVED)->count();

        $resolved = $exceptionQuery()->whereNotNull('resolved_at')->get(['detected_at', 'resolved_at']);
        $avgResolution = $resolved->count() > 0
            ? round($resolved->avg(
                fn ($e) => ($e->resolved_at->getTimestamp() - $e->detected_at->getTimestamp()) / 3600
            ), 1)
            : null;

        $byCarrier = $shipments->groupBy(fn ($s) => $s->carrier->name)->map->count()->sortDesc();

        $percent = fn (int $part, int $whole) => $whole > 0 ? round($part / $whole * 100, 1) : null;

        return [
            'total' => $total,
            'in_transit' => $shipments->where('current_status', Shipment::STATUS_IN_TRANSIT)->count(),
            'delivered' => $delivered,
            'delayed_now' => $delayedNow,
            'open_exceptions' => $openExceptions,
            'delay_rate' => $percent($lateCount, $total),
            'exception_rate' => $percent($withException, $total),
            'on_time_rate' => $percent($deliveredOnTime, $delivered),
            'avg_delay' => $lateCount > 0 ? round($delaySum / $lateCount, 1) : null,
            'avg_resolution' => $avgResolution,
            'status_labels' => $statusLabels,
            'status_values' => $statusValues,
            'carrier_labels' => $byCarrier->keys()->values()->all(),
            'carrier_values' => $byCarrier->values()->all(),
        ];
    }
}