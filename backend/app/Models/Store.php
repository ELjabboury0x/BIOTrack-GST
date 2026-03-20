<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'code',
        'name',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}
