<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceReport extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_CLOSED = 'closed';

    public const TYPE_PREVENTIVE = 'preventive';
    public const TYPE_CURATIVE = 'curative';
    public const TYPE_DIAGNOSTIC = 'diagnostic';

    public const SCOPE_INTERNE = 'interne';
    public const SCOPE_EXTERNE = 'externe';

    protected $fillable = [
        'report_number',
        'intervention_type',
        'intervention_scope',
        'status',
        'intervention_date',
        'started_at',
        'ended_at',
        'duration_minutes',
        'equipment_id',
        'service_id',
        'user_id',
        'engineer_user_id',
        'hospital_name',
        'unit_code',
        'equipment_designation',
        'equipment_serial_number',
        'equipment_inventory_number',
        'supplier_name',
        'brand_name',
        'model_name',
        'problem_description',
        'operations_performed',
        'technician_signature_path',
        'engineer_signature_path',
        'photo_paths',
        'submitted_at',
        'validated_at',
        'closed_at',
    ];

    protected $casts = [
        'intervention_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
        'closed_at' => 'datetime',
        'photo_paths' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (MaintenanceReport $report) {
            if (!$report->report_number) {
                $report->report_number = self::generateReportNumber();
            }

            self::computeDuration($report);
        });

        static::updating(function (MaintenanceReport $report) {
            self::computeDuration($report);
        });
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'engineer_user_id');
    }

    public function canTransitionTo(string $target): bool
    {
        return match ($this->status) {
            self::STATUS_DRAFT => in_array($target, [self::STATUS_SUBMITTED, self::STATUS_VALIDATED, self::STATUS_CLOSED], true),
            self::STATUS_SUBMITTED => in_array($target, [self::STATUS_VALIDATED, self::STATUS_CLOSED], true),
            self::STATUS_VALIDATED => $target === self::STATUS_CLOSED,
            default => false,
        };
    }

    private static function computeDuration(MaintenanceReport $report): void
    {
        if (!$report->started_at || !$report->ended_at) {
            $report->duration_minutes = null;
            return;
        }

        $start = Carbon::parse($report->started_at);
        $end = Carbon::parse($report->ended_at);

        $report->duration_minutes = $end->greaterThan($start) ? $start->diffInMinutes($end) : null;
    }

    private static function generateReportNumber(): string
    {
        $year = now()->year;
        $prefix = 'RII-' . $year . '-';

        $max = (int) DB::table('maintenance_reports')
            ->where('report_number', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(report_number, -5) AS UNSIGNED)) as seq')
            ->value('seq');

        return $prefix . str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
    }
}
