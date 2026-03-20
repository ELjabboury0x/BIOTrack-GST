<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'hospital_id',
        'name',
        'display_order',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('name');
    }

    public function equipments(): HasManyThrough
    {
        return $this->hasManyThrough(Equipment::class, Service::class, 'floor_id', 'service_id');
    }

}
