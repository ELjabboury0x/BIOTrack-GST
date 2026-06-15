<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Hospital;
use Illuminate\Database\QueryException;

try {
    $query = Hospital::query()->select('id','code','name')->orderBy('name');
    $withCount = $query->withCount(['equipments','services']);
    echo "Builder SQL: \n" . $withCount->toSql() . "\n\n";
    // Try to get results
    $rows = $withCount->get();
    echo "Success, rows: " . $rows->count() . "\n";
} catch (QueryException $e) {
    echo "QueryException: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getSql')) {
        echo "SQL: " . $e->getSql() . "\n";
    }
    echo "Bindings: " . json_encode($e->getBindings()) . "\n";
} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
}
