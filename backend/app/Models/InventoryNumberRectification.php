<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryNumberRectification extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'old_inventory_number',
        'new_inventory_number',
        'reason',
        'rectified_at',
        'rectified_by',
    ];

    protected $casts = [
        'rectified_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
