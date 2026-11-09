<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    protected $fillable = ['name', 'code', 'mode', 'delay_threshold_hours'];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}