<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'gst_id',
        'code',
        'name',
    ];

    public function gst(): BelongsTo
    {
        return $this->belongsTo(Gst::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }
}
