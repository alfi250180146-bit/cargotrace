<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_PICKED_UP = 'PICKED_UP';
    public const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    public const STATUS_DELIVERED = 'DELIVERED';

    // current_status dan created_by sengaja TIDAK masuk $fillable:
    // status diatur oleh business rules, bukan input bebas dari form.
    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'cargo_type',
        'weight_kg',
        'volume_cbm',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
    ];

    protected function casts(): array
    {
        return [
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'weight_kg' => 'decimal:2',
            'volume_cbm' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}