<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketEquipmentImportLine extends Model
{
    use HasFactory;

    protected $table = 'market_equipment_import_lines';

    protected $fillable = [
        'market_id',
        'row_signature',
        'market_object',
        'lot_number',
        'article',
        'designation',
        'quantity',
        'delivery_status',
        'delivery_date',
        'market_complaint_status',
        'market_complaint_date',
        'observations',
        'recommendations',
        'source_file_name',
        'source_sheet_name',
        'source_row_index',
    ];

    protected $casts = [
        'quantity' => 'float',
        'delivery_date' => 'date',
        'market_complaint_date' => 'date',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
