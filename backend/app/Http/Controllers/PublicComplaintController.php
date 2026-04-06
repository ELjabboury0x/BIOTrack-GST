<?php

namespace App\Http\Controllers;

use App\Events\ComplaintCreated;
use App\Http\Requests\StorePublicComplaintRequest;
use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\Service;
use App\Services\DashboardMetricsService;
use App\Services\RealtimeMetricsBroadcaster;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDOException;

class PublicComplaintController extends Controller
{
    public function __construct(
        private DashboardMetricsService $dashboardMetricsService,
        private RealtimeMetricsBroadcaster $realtimeMetricsBroadcaster
    ) {}

    public function create(string $service_code)
    {
        try {
            $service = $this->findServiceByCode($service_code);

            if (!$service) {
                return redirect()
                    ->route('public.reclamation.index')
                    ->with('error', 'Code service introuvable. Choisissez un service dans la liste.');
            }

            $equipments = $this->findEquipmentsForService($service);

            return view('public.reclamation-form', [
                'service' => $service,
                'serviceToken' => $service_code,
                'equipments' => $equipments,
            ]);
        } catch (QueryException|PDOException $e) {
            return $this->handlePublicDatabaseException($e, 'create public complaint form');
        }
    }

    public function index()
    {
        try {
            $catalogOrder = $this->catalogOrderByCode();

            $services = Service::query()
                ->excludeHiddenForUi()
                ->get(['id', 'name', 'code'])
                ->filter(fn (Service $service): bool => $this->isAllowedService($service))
                ->sortBy(function (Service $service) use ($catalogOrder): array {
                    $key = $this->normalizeCatalogKey((string) ($service->code ?? ''));

                    return [
                        $catalogOrder[$key] ?? 9999,
                        mb_strtolower((string) ($service->name ?? '')),
                    ];
                })
                ->values();

            return view('public.reclamation-index', [
                'services' => $services,
                'dbUnavailable' => false,
            ]);
        } catch (QueryException|PDOException $e) {
            Log::warning('Public complaint index unavailable due to database error', [
                'message' => $e->getMessage(),
            ]);

            return view('public.reclamation-index', [
                'services' => collect(),
                'dbUnavailable' => true,
            ]);
        }
    }

    public function store(StorePublicComplaintRequest $request, string $service_code)
    {
        try {
            $service = $this->findServiceByCode($service_code);

            if (!$service) {
                return redirect()
                    ->route('public.reclamation.index')
                    ->with('error', 'Code service introuvable. Choisissez un service dans la liste.');
            }

            $selectedEquipmentId = (int) $request->validated('equipment_id');

            $attachments = [];
            foreach ($request->file('attachments', []) as $file) {
                $attachments[] = $file->store('complaints', 'public');
            }

            $complaint = Complaint::query()->create([
                'service_id' => $service->id,
                'equipment_id' => $selectedEquipmentId,
                'reported_by_name' => $request->validated('reported_by_name'),
                'room_number' => $request->validated('room_number'),
                'description' => $request->validated('description'),
                'priority' => $request->validated('priority'),
                'status' => 'open',
                'attachment_path' => $attachments,
            ]);

            $complaintId = (int) $complaint->id;

            $this->dispatchAfterResponse($complaintId);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Votre réclamation a été enregistrée avec succès.',
                    'complaint_id' => $complaintId,
                ], 201);
            }

            return redirect()
                ->route('public.reclamation.form', ['service_code' => $service->code])
                ->with('success', 'Votre réclamation a été enregistrée avec succès.');
        } catch (QueryException|PDOException $e) {
            return $this->handlePublicDatabaseException($e, 'store public complaint', $request->expectsJson());
        }
    }

    private function dispatchAfterResponse(int $complaintId): void
    {
        app()->terminating(function () use ($complaintId) {
            $complaint = Complaint::query()
                ->with([
                    'service:id,code,name',
                    'equipment:id,service_id,inventory_number_current,designation',
                ])
                ->find($complaintId);

            if (!$complaint) {
                return;
            }

            try {
                event(new ComplaintCreated($complaint));
            } catch (\Throwable $e) {
                Log::warning('ComplaintCreated event failed in public complaint flow', [
                    'complaint_id' => $complaintId,
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                $this->realtimeMetricsBroadcaster->broadcastComplaintCreated($complaint);
            } catch (\Throwable $e) {
            }

            try {
                $this->dashboardMetricsService->invalidateCache();
            } catch (\Throwable $e) {
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

    private function findEquipmentsForService(Service $service)
    {
        $serviceName = mb_strtoupper(trim((string) ($service->name ?? '')));
        $serviceCode = mb_strtoupper(trim((string) ($service->code ?? '')));
        $serviceNameToken = $this->normalizeServiceToken($serviceName);
        $serviceCodeToken = $this->normalizeServiceToken($serviceCode);
        $serviceNameExpr = $this->normalizedEquipmentTokenExpression('service_name');
        $unitNameExpr = $this->normalizedEquipmentTokenExpression('unit_name');

        $query = Equipment::query()
            ->select(['id', 'service_id', 'inventory_number_current', 'designation'])
            ->where(function ($query) use ($service, $serviceName, $serviceCode, $serviceNameToken, $serviceCodeToken, $serviceNameExpr, $unitNameExpr) {
                $query->where('service_id', $service->id);

                if ($serviceName !== '') {
                    $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceName])
                        ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceName])
                        ->orWhere('service_name', 'like', '%' . $serviceName . '%')
                        ->orWhere('unit_name', 'like', '%' . $serviceName . '%');
                }

                if ($serviceCode !== '') {
                    $query->orWhereRaw('UPPER(TRIM(service_name)) = ?', [$serviceCode])
                        ->orWhereRaw('UPPER(TRIM(unit_name)) = ?', [$serviceCode])
                        ->orWhere('service_name', 'like', '%' . $serviceCode . '%')
                        ->orWhere('unit_name', 'like', '%' . $serviceCode . '%');
                }

                if ($serviceNameToken !== '') {
                    $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceNameToken . '%'])
                        ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceNameToken . '%']);
                }

                if ($serviceCodeToken !== '') {
                    $query->orWhereRaw($serviceNameExpr . ' like ?', ['%' . $serviceCodeToken . '%'])
                        ->orWhereRaw($unitNameExpr . ' like ?', ['%' . $serviceCodeToken . '%']);
                }
            });

        $items = $query
            ->orderBy('designation')
            ->get()
            ->unique('id')
            ->values();

        return $items;
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
        $allowedCodeKeys = $this->allowedCodeKeySet();
        $codeKey = $this->normalizeCatalogKey((string) ($service->code ?? ''));

        return isset($allowedCodeKeys[$codeKey]);
    }

    private function allowedCodeKeySet(): array
    {
        $set = [];

        foreach ((array) config('hme_public_services', []) as $index => $entry) {
            $code = trim((string) ($entry['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $set[$this->normalizeCatalogKey($code)] = $index;
        }

        return $set;
    }

    private function catalogOrderByCode(): array
    {
        $order = [];

        foreach ((array) config('hme_public_services', []) as $index => $entry) {
            $code = trim((string) ($entry['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $order[$this->normalizeCatalogKey($code)] = $index;
        }

        return $order;
    }

    private function normalizeCatalogKey(string $value): string
    {
        $ascii = Str::upper(Str::ascii(trim($value)));

        return str_replace([' ', '-', '_', '/'], '', $ascii);
    }

    private function handlePublicDatabaseException(QueryException|PDOException $e, string $context, bool $expectsJson = false)
    {
        Log::warning('Public complaint flow unavailable due to database error', [
            'context' => $context,
            'message' => $e->getMessage(),
        ]);

        $message = 'Le service de reclamation est temporairement indisponible. Veuillez reessayer dans quelques instants.';

        if ($expectsJson) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 503);
        }

        return redirect()
            ->route('public.reclamation.index')
            ->with('error', $message);
    }
}
