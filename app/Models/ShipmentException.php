<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    public const TYPES = [
        'CUSTOMS_HOLD',
        'WEATHER_DELAY',
        'PORT_CONGESTION',
        'DAMAGED',
        'MISROUTED',
        'DOCUMENTATION_ISSUE',
    ];

    public const SEVERITIES = ['LOW', 'MEDIUM', 'HIGH'];

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_INVESTIGATING = 'INVESTIGATING';
    public const STATUS_RESOLVED = 'RESOLVED';

    // shipment_id, status, detected_at, resolved_at, created_by sengaja TIDAK masuk $fillable:
    // diisi oleh service, bukan dari input form.
    protected $fillable = ['type', 'severity', 'notes'];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}