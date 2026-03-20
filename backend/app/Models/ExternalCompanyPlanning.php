<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalCompanyPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'planned_date',
        'planned_date_end',
        'contact_person',
        'description',
        'source_file',
        'source_contract',
        'source_quarter',
        'source_hash',
        'status',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'planned_date_end' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Met a jour automatiquement les statuts de planning selon la date du jour:
     * - en_attente: date debut dans le futur
     * - en_cours: date du jour incluse dans l'intervalle [debut, fin]
     * - termine: date fin depassee
     */
    public static function syncAutomaticStatuses(?string $referenceDate = null): void
    {
        $today = $referenceDate ?: now()->toDateString();

        // 1) Tout planning passe en termine lorsque la date de fin est depassee.
        static::query()
            ->where('status', '!=', 'termine')
            ->whereDate('planned_date_end', '<', $today)
            ->update(['status' => 'termine']);

        // 2) Planning en cours si la date du jour est dans l'intervalle [debut, fin].
        static::query()
            ->where('status', '!=', 'termine')
            ->where('status', '!=', 'en_cours')
            ->whereDate('planned_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('planned_date_end')
                    ->orWhereDate('planned_date_end', '>=', $today);
            })
            ->update(['status' => 'en_cours']);

        // 3) Planning en attente si la date de debut est future.
        static::query()
            ->where('status', '!=', 'termine')
            ->where('status', '!=', 'en_attente')
            ->whereDate('planned_date', '>', $today)
            ->update(['status' => 'en_attente']);
    }
}
