<?php

declare(strict_types=1);

use App\Http\Controllers\CompanyPerformanceReportController;
use App\Http\Controllers\ExternalInterventionController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\MaintenanceReportController;
use App\Http\Controllers\OperatorDefectController;
use App\Http\Controllers\PreventiveMaintenanceController;
use App\Http\Controllers\PublicComplaintController;
use App\Models\Equipment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new ViewErrorBag());

$user = User::query()->find(6);
if (!$user) {
    echo "ERROR: user #6 not found" . PHP_EOL;
    exit(1);
}

$totalEquipments = (int) Equipment::query()->count();
$allEquipmentIds = Equipment::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
$equipmentIdSet = array_fill_keys($allEquipmentIds, true);

$makeRequest = static function (string $path = '/dashboard/test') use ($user): Request {
    $request = Request::create($path, 'GET');
    $request->setUserResolver(static fn () => $user);
    return $request;
};

$extractEquipmentCount = static function ($viewData): array {
    $equipments = $viewData['equipments'] ?? null;

    if ($equipments instanceof Collection) {
        $ids = $equipments->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        return ['count' => count($ids), 'ids' => $ids];
    }

    if (is_array($equipments)) {
        $ids = collect($equipments)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        return ['count' => count($ids), 'ids' => $ids];
    }

    return ['count' => -1, 'ids' => []];
};

$cases = [];

$cases[] = [
    'name' => 'SAV create',
    'run' => static fn () => app(ExternalInterventionController::class)->create(),
];

$cases[] = [
    'name' => 'Intervention create',
    'run' => static fn () => app(InterventionController::class)->create($makeRequest('/dashboard/interventions/create')),
];

$cases[] = [
    'name' => 'Maintenance report create',
    'run' => static fn () => app(MaintenanceReportController::class)->create($makeRequest('/dashboard/rapports/interventions-internes/create')),
];

$cases[] = [
    'name' => 'Preventive create',
    'run' => static fn () => app(PreventiveMaintenanceController::class)->create($makeRequest('/dashboard/maintenance-preventive/create')),
];

$cases[] = [
    'name' => 'Operator defect create',
    'run' => static fn () => app(OperatorDefectController::class)->create($makeRequest('/dashboard/operator/defects/create')),
];

$serviceCode = (string) (Service::query()->excludeHiddenForUi()->orderBy('id')->value('code') ?? '');
if ($serviceCode !== '') {
    $cases[] = [
        'name' => 'Public reclamation create',
        'run' => static fn () => app(PublicComplaintController::class)->create($serviceCode),
    ];
}

$cases[] = [
    'name' => 'Company performance index',
    'run' => static fn () => app(CompanyPerformanceReportController::class)->index($makeRequest('/dashboard/rapports/societes-externes/interventions')),
];

echo "Active equipments in DB: {$totalEquipments}" . PHP_EOL;

$failed = false;

foreach ($cases as $case) {
    $name = $case['name'];

    try {
        $view = $case['run']();
        $data = method_exists($view, 'getData') ? $view->getData() : [];
        $equipmentMeta = $extractEquipmentCount($data);

        $invalidIds = [];
        foreach ($equipmentMeta['ids'] as $id) {
            if (!isset($equipmentIdSet[$id])) {
                $invalidIds[] = $id;
            }
        }

        $renderedLength = strlen((string) $view->render());

        echo "[OK] {$name} | render={$renderedLength}";
        if ($equipmentMeta['count'] >= 0) {
            echo " | equipments={$equipmentMeta['count']}";
        }
        if (!empty($invalidIds)) {
            echo " | INVALID_IDS=" . implode(',', $invalidIds);
            $failed = true;
        }
        echo PHP_EOL;
    } catch (Throwable $e) {
        $failed = true;
        echo "[FAIL] {$name} | " . $e->getMessage() . PHP_EOL;
    }
}

exit($failed ? 1 : 0);
