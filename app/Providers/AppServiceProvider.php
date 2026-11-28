<?php

namespace App\Providers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\NotifyObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Audit log: perubahan penting pada data utama
        foreach ([
            Shipment::class,
            ShipmentException::class,
            TrackingEvent::class,
            Carrier::class,
            Location::class,
            Setting::class,
            User::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Notifikasi in-app untuk customer pemilik shipment
        Shipment::observe(NotifyObserver::class);
        ShipmentException::observe(NotifyObserver::class);
    }
}