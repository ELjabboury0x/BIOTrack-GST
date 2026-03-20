<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'inventory_number_current',
        'serial_number',
        'designation',
        'brand_name',
        'model_name',
        'unit_name',
        'sector_name',
        'sector_description',
        'market_label',
        'market_object',
        'lot_number',
        'article',
        'quantity',
        'delivery_reception_provisoire',
        'delivery_status',
        'delivery_date',
        'market_complaint_status',
        'market_complaint_date',
        'observations',
        'recommendations',
        'annual_maintenance_amount_ht',
        'date_reception_provisoire',
        'duree_garantie',
        'date_reception_definitive',
        'manufacture_date',
        'icon_class',
        'category_name',
        'lifecycle_status',
        'description',
        'serial_label_removed',
        'serial_label_comment',
        'service_name',
        'exact_location',
        'operational_status',
        'hospital_id',
        'zone_id',
        'service_id',
        'category_id',
        'room_id',
        'store_id',
        'company_id',
        'market_id',
    ];

    protected $casts = [
        'serial_label_removed' => 'boolean',
        'manufacture_date' => 'date',
        'date_reception_provisoire' => 'date',
        'date_reception_definitive' => 'date',
        'delivery_date' => 'date',
        'market_complaint_date' => 'date',
        'quantity' => 'float',
        'annual_maintenance_amount_ht' => 'float',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function verification()
    {
        return $this->hasOne(EquipmentVerification::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(EquipmentVerificationLog::class);
    }

    public function inventoryRectifications()
    {
        return $this->hasMany(InventoryNumberRectification::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function externalInterventions()
    {
        return $this->hasMany(ExternalIntervention::class);
    }

    public function maintenanceReports()
    {
        return $this->hasMany(MaintenanceReport::class);
    }

    /**
     * Scope: filter equipments visible to given user according to role.
     * - admin / ingenieur => all
     * - technicien / major => only equipments with same service_id as user
     */
    public function scopeVisibleTo($query, $user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            // no authenticated user - return none by default
            return $query->whereRaw('1 = 0');
        }

        $role = $user->role;

        if (in_array($role, ['admin', 'ingenieur'])) {
            return $query; // no filter
        }

        // technicien and major see only their service
        if (in_array($role, ['technicien', 'technician', 'major'])) {
            return $query->where('service_id', $user->service_id);
        }

        // default deny
        return $query->whereRaw('1 = 0');
    }
}
