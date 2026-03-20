<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalIntervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'intervention_id',
        'equipment_id',
        'company_id',
        'service_name',
        'failure_datetime',
        'first_call_datetime',
        'arrival_datetime',
        'technician_arrival_datetime',
        'resolution_datetime',
        'intervention_description',
        'replaced_parts',
        'technician_name',
        'status',
        'intervention_status',
        'resolution_status',
        'intervention_duration_hours',
        'notes',
    ];

    protected $casts = [
        'failure_datetime' => 'datetime',
        'first_call_datetime' => 'datetime',
        'arrival_datetime' => 'datetime',
        'technician_arrival_datetime' => 'datetime',
        'resolution_datetime' => 'datetime',
        'intervention_duration_hours' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (ExternalIntervention $model) {
            if (!$model->arrival_datetime && $model->technician_arrival_datetime) {
                $model->arrival_datetime = $model->technician_arrival_datetime;
            }

            if (!$model->technician_arrival_datetime && $model->arrival_datetime) {
                $model->technician_arrival_datetime = $model->arrival_datetime;
            }

            if (!$model->status && $model->intervention_status) {
                $model->status = $model->intervention_status;
            }

            if (!$model->intervention_status && $model->status) {
                $model->intervention_status = $model->status;
            }

            if ($model->first_call_datetime && $model->resolution_datetime && $model->resolution_datetime->greaterThanOrEqualTo($model->first_call_datetime)) {
                $minutes = $model->first_call_datetime->diffInMinutes($model->resolution_datetime);
                $model->intervention_duration_hours = round($minutes / 60, 2);
            }
        });
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function logs()
    {
        return $this->hasMany(ExternalInterventionLog::class);
    }
}
