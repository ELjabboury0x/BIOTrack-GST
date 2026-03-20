<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function markets()
    {
        return $this->hasMany(Market::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function externalInterventions()
    {
        return $this->hasMany(ExternalIntervention::class);
    }
}
