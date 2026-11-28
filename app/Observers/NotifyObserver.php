<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Notifications\ShipmentNotification;
use Illuminate\Database\Eloquent\Model;

class NotifyObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof ShipmentException) {
            $shipment = $model->shipment;

            $this->send($shipment, 'exception', sprintf(
                'Exception reported on %s: %s (%s).',
                $shipment?->shipment_number,
                str_replace('_', ' ', $model->type),
                $model->severity
            ));
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof Shipment) {
            if ($model->wasChanged('current_status')) {
                $this->send($model, 'status', sprintf(
                    'Shipment %s is now %s.',
                    $model->shipment_number,
                    str_replace('_', ' ', $model->current_status)
                ));
            }

            if ($model->wasChanged('estimated_arrival')
                && $model->estimated_arrival
                && $model->estimated_arrival->gt($model->expected_arrival)) {
                $hours = round(
                    ($model->estimated_arrival->getTimestamp() - $model->expected_arrival->getTimestamp()) / 3600,
                    1
                );

                $this->send($model, 'delay', sprintf(
                    'Shipment %s is now expected %s h later than planned (ETA %s).',
                    $model->shipment_number,
                    number_format($hours, 1),
                    $model->estimated_arrival->format('d M Y H:i')
                ));
            }
        }

        if ($model instanceof ShipmentException && $model->wasChanged('status')) {
            $shipment = $model->shipment;

            $this->send($shipment, 'exception', sprintf(
                'Exception on %s is now %s.',
                $shipment?->shipment_number,
                $model->status
            ));
        }
    }

    private function send(?Shipment $shipment, string $kind, string $message): void
    {
        $customer = $shipment?->customer;

        if (! $customer) {
            return;
        }

        $customer->notify(new ShipmentNotification($shipment, $kind, $message));
    }
}