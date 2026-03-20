<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'hospital_id')) {
                $table->foreignId('hospital_id')->nullable()->after('zone_id')->constrained('hospitals')->nullOnDelete();
                $table->index('hospital_id');
            }
        });

        Schema::table('equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('equipments', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('service_id')->constrained('categories')->nullOnDelete();
                $table->index(['hospital_id', 'service_id', 'category_id'], 'equipments_hospital_service_category_idx');
            }
        });

        $now = now();

        DB::table('hospitals')->updateOrInsert(
            ['code' => 'HME'],
            ['name' => 'Hopital Mere-Enfants', 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('hospitals')->updateOrInsert(
            ['code' => 'HO'],
            ['name' => 'Hopital des Specialites', 'updated_at' => $now, 'created_at' => $now]
        );

        $hmeId = (int) DB::table('hospitals')->where('code', 'HME')->value('id');
        $hoId = (int) DB::table('hospitals')->where('code', 'HO')->value('id');

        $fallbackZoneId = (int) DB::table('zones')->value('id');
        if ($fallbackZoneId <= 0) {
            DB::table('zones')->insert([
                'name' => 'Zone generale',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $fallbackZoneId = (int) DB::table('zones')->where('name', 'Zone generale')->value('id');
        }

        $hmeServices = [
            ['name' => 'Reanimation Pediatrique', 'code' => 'RPE'],
            ['name' => 'Urgences Pediatriques', 'code' => 'URP'],
            ['name' => 'Consultations et Explorations Fonctionnelles Pediatriques', 'code' => 'CEFP'],
            ['name' => 'Chirurgie Pediatrique Traumato-orthopedique', 'code' => 'TOP'],
            ['name' => 'Chirurgie Pediatrique Urologique-Viscerale', 'code' => 'UVP'],
            ['name' => 'Neonatologie - Reanimation neonatale', 'code' => 'NEO'],
            ['name' => 'Pediatrie', 'code' => 'PED'],
            ['name' => 'Unite d\'Oncologie Pediatrique', 'code' => 'UOP'],
            ['name' => 'Unite Technique d\'Accouchement', 'code' => 'UTA'],
            ['name' => 'Unite de Gynecologie', 'code' => 'GYN'],
            ['name' => 'Unite d\'Obstetrique', 'code' => 'OBS'],
            ['name' => 'Unite de PMA', 'code' => 'PMA'],
            ['name' => 'Bloc Operatoire Central - Module 3', 'code' => 'BOC M3'],
            ['name' => 'Bloc Operatoire Central - Module 4', 'code' => 'BOC M4'],
            ['name' => 'Bloc Operatoire Central - Reveil Enfant', 'code' => 'BOC RVE'],
        ];

        $hoServices = [
            ['name' => 'CCV', 'code' => 'CCV'],
            ['name' => 'Chirurgie Thoracique', 'code' => 'CTH'],
            ['name' => 'Chirurgie Traumato-Orthopedique', 'code' => 'CTO'],
            ['name' => 'Chirurgie Vasculaire', 'code' => 'CVA'],
            ['name' => 'Chirurgie Viscerale', 'code' => 'CVI'],
            ['name' => 'Gastro-enterologie', 'code' => 'GAS'],
            ['name' => 'Intubation', 'code' => 'INT'],
            ['name' => 'Neurochirurgie', 'code' => 'NEU'],
            ['name' => 'Ophtalmologie', 'code' => 'OPH'],
            ['name' => 'ORL', 'code' => 'ORL'],
            ['name' => 'Pneumologie', 'code' => 'PNE'],
            ['name' => 'Radiologie', 'code' => 'RAD'],
            ['name' => 'Laboratoire', 'code' => 'LAB'],
            ['name' => 'SAMLI Urgences Anesthesie Reanimation', 'code' => 'SAMLI'],
            ['name' => 'Urologie', 'code' => 'URO'],
        ];

        $upsertService = static function (array $service, int $hospitalId) use ($fallbackZoneId, $now): void {
            $serviceCode = trim((string) ($service['code'] ?? ''));
            $serviceName = trim((string) ($service['name'] ?? ''));

            $existingId = null;
            if ($serviceCode !== '') {
                $existingId = DB::table('services')
                    ->whereRaw('LOWER(TRIM(code)) = ?', [mb_strtolower($serviceCode)])
                    ->value('id');
            }

            if (!$existingId && $serviceName !== '') {
                $existingId = DB::table('services')
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($serviceName)])
                    ->value('id');
            }

            if ($existingId) {
                DB::table('services')->where('id', $existingId)->update([
                    'hospital_id' => $hospitalId,
                    'zone_id' => $fallbackZoneId,
                    'name' => $serviceName,
                    'code' => $serviceCode,
                    'updated_at' => $now,
                ]);
                return;
            }

            DB::table('services')->insert([
                'zone_id' => $fallbackZoneId,
                'hospital_id' => $hospitalId,
                'name' => $serviceName,
                'code' => $serviceCode,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };

        foreach ($hmeServices as $service) {
            $upsertService($service, $hmeId);
        }

        foreach ($hoServices as $service) {
            $upsertService($service, $hoId);
        }

        if ($hmeId > 0) {
            // Important: conserver les equipements existants et les rattacher a HME.
            DB::table('equipments')->update(['hospital_id' => $hmeId]);
        }

        $laboratoireServiceId = (int) DB::table('services')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['laboratoire'])
            ->orderByDesc('id')
            ->value('id');

        if ($laboratoireServiceId > 0) {
            $labCategories = ['Biochimie', 'Hematologie', 'Microbiologie', 'Immunologie', 'Parasitologie', 'Serologie'];
            foreach ($labCategories as $categoryName) {
                DB::table('categories')->updateOrInsert(
                    ['service_id' => $laboratoireServiceId, 'name' => $categoryName],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        // Tentative de rattachement automatique des equipements selon service_name et category_name.
        $servicesByName = DB::table('services')
            ->select('id', 'hospital_id', 'name')
            ->get()
            ->keyBy(fn ($row) => mb_strtolower(trim((string) $row->name)));

        $categoriesByService = DB::table('categories')
            ->select('id', 'service_id', 'name')
            ->get()
            ->groupBy('service_id')
            ->map(function ($rows) {
                return collect($rows)->keyBy(fn ($row) => mb_strtolower(trim((string) $row->name)));
            });

        DB::table('equipments')
            ->select('id', 'service_name', 'category_name', 'service_id')
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use ($servicesByName, $categoriesByService): void {
                foreach ($chunk as $equipment) {
                    $serviceName = mb_strtolower(trim((string) ($equipment->service_name ?? '')));
                    $categoryName = mb_strtolower(trim((string) ($equipment->category_name ?? '')));

                    $serviceId = (int) ($equipment->service_id ?? 0);
                    if ($serviceId <= 0 && $serviceName !== '' && $servicesByName->has($serviceName)) {
                        $serviceId = (int) $servicesByName->get($serviceName)->id;
                    }

                    $update = [];
                    if ($serviceId > 0 && (int) ($equipment->service_id ?? 0) <= 0) {
                        $update['service_id'] = $serviceId;
                    }

                    if ($serviceId > 0 && $categoryName !== '' && $categoriesByService->has($serviceId)) {
                        $categoryMap = $categoriesByService->get($serviceId);
                        $category = $categoryMap->get($categoryName);
                        if ($category) {
                            $update['category_id'] = (int) $category->id;
                        }
                    }

                    if ($update !== []) {
                        DB::table('equipments')->where('id', $equipment->id)->update($update);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'category_id')) {
                $table->dropIndex('equipments_hospital_service_category_idx');
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'hospital_id')) {
                $table->dropIndex(['hospital_id']);
                $table->dropConstrainedForeignId('hospital_id');
            }
        });
    }
};
