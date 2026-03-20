<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Structure extends Model
{
    use HasFactory;

    public const TYPE_GST = 'gst';
    public const TYPE_BRANCHE = 'branche';
    public const TYPE_DIRECTION = 'direction';
    public const TYPE_HOPITAL = 'hopital';
    public const TYPE_BATIMENT = 'batiment';
    public const TYPE_ETAGE = 'etage';
    public const TYPE_SERVICE = 'service';
    public const TYPE_UNITE = 'unite';

    public const ALLOWED_TYPES = [
        self::TYPE_GST,
        self::TYPE_BRANCHE,
        self::TYPE_DIRECTION,
        self::TYPE_HOPITAL,
        self::TYPE_BATIMENT,
        self::TYPE_ETAGE,
        self::TYPE_SERVICE,
        self::TYPE_UNITE,
    ];

    protected $fillable = [
        'parent_id',
        'name',
        'nom',
        'type',
        'code',
        'responsable',
        'order',
        'ordre',
    ];

    protected $casts = [
        'order' => 'integer',
        'ordre' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order')->orderBy('name');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->ordered();
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
