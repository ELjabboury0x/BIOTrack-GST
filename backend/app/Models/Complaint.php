<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Complaint extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'service_id',
        'equipment_id',
        'reported_by_name',
        'room_number',
        'description',
        'priority',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'attachment_path' => 'array',
        'created_at' => 'datetime',
    ];

    public function getAttachmentPathAttribute($value): array
    {
        if (is_array($value)) {
            return $this->normalizeAttachmentArray($value);
        }

        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return $this->normalizeAttachmentArray($decoded);
                }

                if (is_string($decoded) && trim($decoded) !== '') {
                    return $this->normalizeAttachmentArray([$decoded]);
                }

                return [];
            }

            return $this->normalizeAttachmentArray([$trimmed]);
        }

        return [];
    }

    private function normalizeAttachmentArray(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->map(function (string $item) {
                $path = trim($item);

                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return $path;
                }

                $path = str_replace('\\\\', '/', ltrim($path, '/'));

                if (str_starts_with($path, 'public/')) {
                    return substr($path, 7);
                }

                if (str_starts_with($path, 'storage/public/')) {
                    return substr($path, 15);
                }

                return $path;
            })
            ->unique()
            ->values()
            ->all();
    }

    public static function attachmentUrl(?string $path): ?string
    {
        $raw = trim((string) $path);
        if ($raw === '') {
            return null;
        }

        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return $raw;
        }

        $normalized = str_replace('\\\\', '/', ltrim($raw, '/'));

        if (str_starts_with($normalized, 'storage/')) {
            return '/' . $normalized;
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/public/')) {
            $normalized = substr($normalized, 15);
        }

        return Storage::disk('public')->url($normalized);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function setPriorityAttribute($value): void
    {
        $raw = strtolower(trim((string) $value));

        $this->attributes['priority'] = match ($raw) {
            'normal', 'medium', 'moyenne' => 'medium',
            'urgent' => 'urgent',
            'high', 'haute' => 'high',
            'low', 'basse' => 'low',
            default => 'medium',
        };
    }

    /**
     * Supprime les fichiers d'images attachés du stockage et vide le champ en base.
     */
    public function deleteAttachments(): int
    {
        $raw = $this->getRawOriginal('attachment_path');
        $paths = is_string($raw) ? (json_decode($raw, true) ?? []) : (is_array($raw) ? $raw : []);

        $deleted = 0;
        foreach ($paths as $path) {
            if (!is_string($path) || trim($path) === '') {
                continue;
            }

            $normalized = str_replace('\\', '/', ltrim(trim($path), '/'));

            if (filter_var($normalized, FILTER_VALIDATE_URL)) {
                continue;
            }

            $storagePath = str_starts_with($normalized, 'public/')
                ? substr($normalized, 7)
                : $normalized;

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
                $deleted++;
            }
        }

        if ($deleted > 0 || !empty($paths)) {
            $this->newQuery()->where('id', $this->id)->update(['attachment_path' => null]);
        }

        return $deleted;
    }
}
