<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'room_number',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}
