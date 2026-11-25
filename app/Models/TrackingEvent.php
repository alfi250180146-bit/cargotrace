<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    public const TYPE_STATUS_CHANGE = 'STATUS_CHANGE';
    public const TYPE_CHECKPOINT = 'CHECKPOINT';
    public const TYPE_EXCEPTION = 'EXCEPTION';

    public const TYPES = [
        self::TYPE_STATUS_CHANGE,
        self::TYPE_CHECKPOINT,
        self::TYPE_EXCEPTION,
    ];

    // shipment_id, is_exception, dan created_by sengaja TIDAK masuk $fillable:
    // diisi oleh service, bukan dari input form.
    protected $fillable = [
        'event_type',
        'status',
        'occurred_at',
        'location_id',
        'latitude',
        'longitude',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'is_exception' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}