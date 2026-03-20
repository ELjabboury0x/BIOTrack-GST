<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalInterventionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_intervention_id',
        'user_id',
        'action_type',
        'from_status',
        'to_status',
        'payload',
        'logged_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'logged_at' => 'datetime',
    ];

    public function externalIntervention()
    {
        return $this->belongsTo(ExternalIntervention::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
