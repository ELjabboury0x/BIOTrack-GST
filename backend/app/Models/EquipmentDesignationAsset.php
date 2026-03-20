<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentDesignationAsset extends Model
{
    use HasFactory;

    protected $table = 'equipment_designation_assets';

    protected $fillable = [
        'designation',
        'image_path',
        'user_manual_path',
        'technical_manual_path',
    ];
}
