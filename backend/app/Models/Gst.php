<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gst extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
    ];

    public function hospitals(): HasMany
    {
        return $this->hasMany(Hospital::class);
    }
}
