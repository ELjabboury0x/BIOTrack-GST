<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BdProfilesUsersSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['sheet' => 'Majors', 'login' => 'ZERKOUNI.HOUDA', 'password' => '123456', 'code' => 'RPE', 'service' => 'Réanimation Pédiatrique'],
            ['sheet' => 'Majors', 'login' => 'IHADJITANE.MALAK', 'password' => '123456', 'code' => 'URP', 'service' => 'Urgences pédiatriques'],
            ['sheet' => 'Majors', 'login' => 'IHADJITANE.MALAK', 'password' => '123456', 'code' => 'CEFP', 'service' => 'Consultations et Explorations Fonctionnelles Pédiatriques'],
            ['sheet' => 'Majors', 'login' => 'KHANTOUR.MOHAMED', 'password' => '123456', 'code' => 'TOP', 'service' => 'Chirurgie Pédiatrique Traumato-orthopédique'],
            ['sheet' => 'Majors', 'login' => 'JABRANE.LATIFA', 'password' => '123456', 'code' => 'UVP', 'service' => 'Chirurgie Pédiatrique Urologique-Viscérale'],
            ['sheet' => 'Majors', 'login' => 'MESRAR.ASMAE', 'password' => '123456', 'code' => 'NEO', 'service' => 'Néonatologie (Réanimation néonatale)'],
            ['sheet' => 'Majors', 'login' => 'NAWAL', 'password' => '123456', 'code' => 'PED', 'service' => 'Pédiatrie'],
            ['sheet' => 'Majors', 'login' => 'NAWAL', 'password' => '123456', 'code' => 'UOP', 'service' => "Unité d'Oncologie Pédiatrique"],
            ['sheet' => 'Majors', 'login' => 'AHADDOUT.HANAE', 'password' => '123456', 'code' => 'UTA', 'service' => "Unité Technique d'Accouchement"],
            ['sheet' => 'Majors', 'login' => 'AHADDOUT.HANAE', 'password' => '123456', 'code' => 'GYN', 'service' => 'Unité de gynécologie'],
            ['sheet' => 'Majors', 'login' => 'AHADDOUT.HANAE', 'password' => '123456', 'code' => 'OBS', 'service' => "Unité d'obstétrique"],
            ['sheet' => 'Majors', 'login' => 'AHADDOUT.HANAE', 'password' => '123456', 'code' => 'PMA', 'service' => 'Unité de PMA (Procréation Médicalement Assistée)'],
            ['sheet' => 'Majors', 'login' => 'SAKROUHI.SAID', 'password' => '123456', 'code' => 'BOC M3', 'service' => 'Bloc Opératoire Central - Module 3 (Chirurgie pédiatrique)'],
            ['sheet' => 'Majors', 'login' => 'SAKROUHI.SAID', 'password' => '123456', 'code' => 'BOC M4', 'service' => 'Bloc Opératoire Central - Module 4 (Césarienne)'],
            ['sheet' => 'Majors', 'login' => 'SAKROUHI.SAID', 'password' => '123456', 'code' => 'BOC RVE', 'service' => 'Bloc Opératoire Central - Réveil Enfant'],
            ['sheet' => 'Techniciens', 'login' => 'BENADDI.FATIMA', 'password' => '123456', 'code' => 'HUME', 'service' => 'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger'],
            ['sheet' => 'Techniciens', 'login' => 'ZOUIN.MAROUANE', 'password' => '123456', 'code' => 'HUME', 'service' => 'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger'],
            ['sheet' => 'Ingénieurs', 'login' => 'KHALIL.HAMZA', 'password' => '123456', 'code' => 'HUME', 'service' => 'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger'],
        ];

        foreach ($rows as $row) {
            $role = match ($row['sheet']) {
                'Majors' => User::ROLE_MAJOR,
                'Techniciens' => User::ROLE_TECHNICIEN,
                'Ingénieurs' => User::ROLE_INGENIEUR,
                default => User::ROLE_ADMIN,
            };

            $service = $this->resolveService($row['code'], $row['service']);
            $email = $this->buildEmailFromLogin($row['login']);

            $user = User::query()->firstOrNew(['login' => $row['login']]);
            $user->name = Str::title(str_replace('.', ' ', strtolower($row['login'])));
            $user->email = $user->email ?: $email;
            $user->password = Hash::make($row['password']);
            $user->role = $role;
            $user->service_id = $user->service_id ?: $service->id;
            $user->must_change_password = true;
            $user->password_changed_at = null;
            $user->save();

            $user->services()->syncWithoutDetaching([$service->id]);
        }
    }

    private function resolveService(string $code, string $serviceName): Service
    {
        $zone = Zone::query()->firstOrCreate(
            ['name' => $this->resolveZoneName($code)],
            ['description' => 'Zone créée automatiquement depuis l’import BD Profiles.']
        );

        return Service::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $serviceName,
                'zone_id' => $zone->id,
            ]
        );
    }

    private function resolveZoneName(string $serviceCode): string
    {
        return match (true) {
            in_array($serviceCode, ['RPE', 'URP', 'CEFP', 'TOP', 'UVP', 'NEO', 'PED', 'UOP'], true) => 'Pôle Pédiatrique',
            in_array($serviceCode, ['UTA', 'GYN', 'OBS', 'PMA'], true) => 'Pôle Gynéco-Obstétrique et Maternité',
            in_array($serviceCode, ['BOC M3', 'BOC M4', 'BOC RVE'], true) => 'Bloc Opératoire Central',
            $serviceCode === 'HUME' => 'Administration Biomédicale',
            default => 'Services généraux',
        };
    }

    private function buildEmailFromLogin(string $login): string
    {
        $normalized = Str::of($login)
            ->lower()
            ->replace('.', '_')
            ->replace(' ', '_')
            ->value();

        return $normalized . '@gst.local';
    }
}
