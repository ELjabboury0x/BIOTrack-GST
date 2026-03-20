<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentVerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'old_status',
        'new_status',
        'changed_at',
        'changed_by',
        'note',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
