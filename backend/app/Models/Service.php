<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory;

    public const UI_HIDDEN_NAMES = [
        '7 LIT',
        '7 LITS',
        'Direction générale',
        'Direction generale',
        'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger',
        'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger',
    ];

    protected $fillable = [
        'code',
        'zone_id',
        'hospital_id',
        'floor_id',
        'name',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_user')->withTimestamps();
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function maintenanceReports(): HasMany
    {
        return $this->hasMany(MaintenanceReport::class);
    }

    public function interventions(): HasManyThrough
    {
        return $this->hasManyThrough(Intervention::class, Equipment::class, 'service_id', 'equipment_id');
    }

    public function scopeExcludeHiddenForUi(Builder $query): Builder
    {
        $hiddenNames = array_values(array_filter(array_map(
            static fn (string $name): string => mb_strtolower(trim($name)),
            self::UI_HIDDEN_NAMES
        )));

        if ($hiddenNames === []) {
            return $query;
        }

        return $query->whereRaw('LOWER(TRIM(name)) NOT IN (' . implode(',', array_fill(0, count($hiddenNames), '?')) . ')', $hiddenNames);
    }
}
