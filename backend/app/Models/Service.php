<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
            $filteredQuery = $query;
        } else {
            $placeholders = implode(',', array_fill(0, count($hiddenNames), '?'));
            $filteredQuery = $query->whereRaw('LOWER(TRIM(name)) NOT IN (' . $placeholders . ')', $hiddenNames);
        }

        $catalog = collect((array) config('hme_public_services', []));

        $normalizedCodes = $catalog
            ->pluck('code')
            ->map(fn ($value) => $this->normalizeCatalogToken((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $normalizedNames = $catalog
            ->pluck('name')
            ->map(fn ($value) => $this->normalizeCatalogToken((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($normalizedCodes === [] && $normalizedNames === []) {
            return $filteredQuery;
        }

        $normalizedCodeExpression = "REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(code)), ' ', ''), '-', ''), '_', ''), '/', '')";
        $normalizedNameExpression = "REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(name)), ' ', ''), '-', ''), '_', ''), '/', '')";

        return $filteredQuery->where(function (Builder $innerQuery) use ($normalizedCodes, $normalizedNames, $normalizedCodeExpression, $normalizedNameExpression): void {
            if ($normalizedCodes !== []) {
                $codePlaceholders = implode(',', array_fill(0, count($normalizedCodes), '?'));
                $innerQuery->whereRaw($normalizedCodeExpression . ' IN (' . $codePlaceholders . ')', $normalizedCodes);
            }

            if ($normalizedNames !== []) {
                $namePlaceholders = implode(',', array_fill(0, count($normalizedNames), '?'));
                $innerQuery->orWhereRaw($normalizedNameExpression . ' IN (' . $namePlaceholders . ')', $normalizedNames);
            }
        });
    }

    private function normalizeCatalogToken(string $value): string
    {
        $ascii = Str::upper(Str::ascii(trim($value)));

        return str_replace([' ', '-', '_', '/'], '', $ascii);
    }
}
