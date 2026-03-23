<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'quantity',
        'unit_price',
        'supplier',
        'phase',
        'entry_mode',
        'discharge_date',
        'return_date',
        'serial_number',
        'action_user_id',
        'assistant_technician_id',
        'service_id',
        'major_signer_id',
        'return_technician_id',
        'condition_state',
        'comment',
        'document_pdf_path',
    ];

    protected $casts = [
        'discharge_date' => 'date',
        'return_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function actionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_user_id');
    }

    public function assistantTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_technician_id');
    }

    public function majorSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'major_signer_id');
    }

    public function returnTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_technician_id');
    }
}
