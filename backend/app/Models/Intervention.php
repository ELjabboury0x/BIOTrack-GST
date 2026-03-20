<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'equipment_id',
        'complaint_id',
        'technician_name',
        'type',
        'maintenance_scope',
        'status',
        'date_start',
        'date_end',
        'closure_type',
        'failure_cause',
        'closure_note',
        'closed_by_name',
        'closed_at',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'closed_at' => 'datetime',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function externalIntervention()
    {
        return $this->hasOne(ExternalIntervention::class);
    }
}
