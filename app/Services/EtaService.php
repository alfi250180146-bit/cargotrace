<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Support\Carbon;

class EtaService
{
    public const DEFAULTS = [
        'delay_threshold_hours' => 12,
        'severity_low_max_hours' => 6,
        'severity_medium_max_hours' => 24,
    ];

    public function setting(string $key): float
    {
        return (float) Setting::getValue($key, self::DEFAULTS[$key]);
    }

    /**
     * Threshold per carrier kalau diisi, kalau tidak pakai threshold default.
     */
    public function thresholdFor(Shipment $shipment): float
    {
        $override = $shipment->carrier?->delay_threshold_hours;

        return $override !== null
            ? (float) $override
            : $this->setting('delay_threshold_hours');
    }

    /**
     * 0 jam atau kurang: tidak delay (null). Di atas itu: LOW / MEDIUM / HIGH.
     */
    public function severityFor(float $delayHours): ?string
    {
        if ($delayHours <= 0) {
            return null;
        }
        if ($delayHours <= $this->setting('severity_low_max_hours')) {
            return 'LOW';
        }
        if ($delayHours <= $this->setting('severity_medium_max_hours')) {
            return 'MEDIUM';
        }

        return 'HIGH';
    }

    /**
     * Hitung status delay satu shipment, lengkap dengan alasan dan angka yang dipakai.
     */
    public function analyze(Shipment $shipment): array
    {
        $expected = $shipment->expected_arrival;
        $arrival = $shipment->estimated_arrival ?? $expected;
        $basis = 'ETA';

        // Kalau sudah DELIVERED, pakai waktu tiba sebenarnya dari tracking event
        if ($shipment->current_status === Shipment::STATUS_DELIVERED) {
            $deliveredAt = TrackingEvent::where('shipment_id', $shipment->id)
                ->where('event_type', TrackingEvent::TYPE_STATUS_CHANGE)
                ->where('status', Shipment::STATUS_DELIVERED)
                ->max('occurred_at');

            if ($deliveredAt) {
                $arrival = Carbon::parse($deliveredAt);
                $basis = 'Actual delivery';
            }
        }

        $delayHours = round(($arrival->getTimestamp() - $expected->getTimestamp()) / 3600, 1);
        $isDelayed = $delayHours > 0;
        $severity = $this->severityFor($delayHours);
        $threshold = $this->thresholdFor($shipment);
        $overThreshold = $delayHours > $threshold;

        $low = $this->setting('severity_low_max_hours');
        $medium = $this->setting('severity_medium_max_hours');

        if (! $isDelayed) {
            $reason = sprintf(
                '%s (%s) is on or before the expected arrival (%s). Not delayed.',
                $basis,
                $arrival->format('d M Y H:i'),
                $expected->format('d M Y H:i')
            );
        } else {
            $reason = sprintf(
                '%s (%s) is %s h later than the expected arrival (%s). Severity %s '
                .'(LOW up to %s h, MEDIUM up to %s h, HIGH above). '
                .'Threshold for this carrier: %s h (%s).',
                $basis,
                $arrival->format('d M Y H:i'),
                number_format($delayHours, 1),
                $expected->format('d M Y H:i'),
                $severity,
                $low + 0,
                $medium + 0,
                $threshold + 0,
                $overThreshold ? 'exceeded' : 'not exceeded'
            );
        }

        return [
            'basis' => $basis,
            'expected_arrival' => $expected,
            'arrival' => $arrival,
            'delay_hours' => $delayHours,
            'is_delayed' => $isDelayed,
            'severity' => $severity,
            'threshold_hours' => $threshold,
            'over_threshold' => $overThreshold,
            'reason' => $reason,
        ];
    }
}