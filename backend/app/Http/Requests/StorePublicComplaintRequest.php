<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePublicComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_by_name' => ['required', 'string', 'max:150'],
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'room_number' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:4000'],
            'priority' => ['required', 'in:normal,urgent'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $serviceCode = (string) $this->route('service_code');
            $equipmentId = (int) $this->input('equipment_id');

            $service = $this->findServiceByCode($serviceCode);

            if (!$service) {
                $validator->errors()->add('service_code', 'Service introuvable.');
                return;
            }

            if ($equipmentId > 0) {
                $serviceName = mb_strtoupper(trim((string) ($service->name ?? '')));
                $serviceCodeDb = mb_strtoupper(trim((string) ($service->code ?? '')));
                $serviceNameToken = $this->normalizeServiceToken($serviceName);
                $serviceCodeToken = $this->normalizeServiceToken($serviceCodeDb);
                $serviceNameExpr = $this->normalizedEquipmentTokenExpression('service_name');
                $unitNameExpr = $this->normalizedEquipmentTokenExpression('unit_name');

                $belongsToService = Equipment::query()
                    ->where('id', $equipmentId)
                    ->where(function ($query) use ($service, $serviceName, $serviceCodeDb, $serviceNameToken, $serviceCodeToken, $serviceNameExpr, $unitNameExpr) {
                        $query->where('service_id', $service->id);

                        if ($serviceName !== '') {
                            $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceName])
                                ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceName])
                                ->orWhere('service_name', 'like', '%' . $serviceName . '%')
                                ->orWhere('unit_name', 'like', '%' . $serviceName . '%');
                        }

                        if ($serviceCodeDb !== '') {
                            $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceCodeDb])
                                ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceCodeDb])
                                ->orWhere('service_name', 'like', '%' . $serviceCodeDb . '%')
                                ->orWhere('unit_name', 'like', '%' . $serviceCodeDb . '%');
                        }

                        if ($serviceNameToken !== '') {
                            $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceNameToken . '%'])
                                ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceNameToken . '%']);
                        }

                        if ($serviceCodeToken !== '') {
                            $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceCodeToken . '%'])
                                ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceCodeToken . '%']);
                        }
                    })
                    ->exists();

                $hasAnyMappedEquipment = Equipment::query()
                    ->where(function ($query) use ($service, $serviceName, $serviceCodeDb, $serviceNameToken, $serviceCodeToken, $serviceNameExpr, $unitNameExpr) {
                        $query->where('service_id', $service->id);

                        if ($serviceName !== '') {
                            $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceName])
                                ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceName])
                                ->orWhere('service_name', 'like', '%' . $serviceName . '%')
                                ->orWhere('unit_name', 'like', '%' . $serviceName . '%');
                        }

                        if ($serviceCodeDb !== '') {
                            $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceCodeDb])
                                ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceCodeDb])
                                ->orWhere('service_name', 'like', '%' . $serviceCodeDb . '%')
                                ->orWhere('unit_name', 'like', '%' . $serviceCodeDb . '%');
                        }

                        if ($serviceNameToken !== '') {
                            $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceNameToken . '%'])
                                ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceNameToken . '%']);
                        }

                        if ($serviceCodeToken !== '') {
                            $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceCodeToken . '%'])
                                ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceCodeToken . '%']);
                        }
                    })
                    ->exists();

                if (!$belongsToService && $hasAnyMappedEquipment) {
                    $validator->errors()->add('equipment_id', 'Cet équipement ne correspond pas au service demandé.');
                }
            }
        });
    }

    private function findServiceByCode(string $serviceCode): ?Service
    {
        $normalizedCode = mb_strtoupper(trim($serviceCode));

        if ($normalizedCode === '') {
            return null;
        }

        if (preg_match('/^ID\-(\d+)$/i', $normalizedCode, $match) === 1) {
            $service = Service::query()->excludeHiddenForUi()->find((int) $match[1]);

            return $service && $this->isAllowedService($service) ? $service : null;
        }

        if (preg_match('/^\d+$/', $normalizedCode) === 1) {
            $service = Service::query()->excludeHiddenForUi()->find((int) $normalizedCode);

            return $service && $this->isAllowedService($service) ? $service : null;
        }

        $service = Service::query()
            ->excludeHiddenForUi()
            ->whereRaw('UPPER(TRIM(code)) = ?', [$normalizedCode])
            ->first();

        return $service && $this->isAllowedService($service) ? $service : null;
    }

    private function normalizeServiceToken(string $value): string
    {
        $ascii = Str::upper(Str::ascii(trim($value)));

        return preg_replace('/[^A-Z0-9]+/', '', $ascii) ?? '';
    }

    private function normalizedEquipmentTokenExpression(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM($column)), ' ', ''), '-', ''), '_', ''), '/', ''), '\'', ''), '’', ''), '.', ''), ',', '')";
    }

    private function isAllowedService(Service $service): bool
    {
        $allowed = [];
        foreach ((array) config('hme_public_services', []) as $entry) {
            $code = trim((string) ($entry['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $allowed[$this->normalizeServiceToken($code)] = true;
        }

        return isset($allowed[$this->normalizeServiceToken((string) ($service->code ?? ''))]);
    }
}
