<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Notifications\Notification;

class ShipmentNotification extends Notification
{
    public function __construct(
        public Shipment $shipment,
        public string $kind,
        public string $message,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'shipment_id' => $this->shipment->id,
            'shipment_number' => $this->shipment->shipment_number,
            'kind' => $this->kind,
            'message' => $this->message,
        ];
    }
}