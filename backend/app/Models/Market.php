<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'market_number',
        'market_date',
        'company_id',
        'source_file_name',
    ];

    protected $casts = [
        'market_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function importLines()
    {
        return $this->hasMany(MarketEquipmentImportLine::class);
    }

    public function equipmentVerifications()
    {
        return $this->hasMany(EquipmentVerification::class, 'source_market_id');
    }
}
