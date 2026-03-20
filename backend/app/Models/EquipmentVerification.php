<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'status',
        'verified_at',
        'source_market_id',
        'note',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function sourceMarket()
    {
        return $this->belongsTo(Market::class, 'source_market_id');
    }
}
