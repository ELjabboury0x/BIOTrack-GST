<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Equipment;
use Illuminate\Database\QueryException;

try {
    $query = Equipment::query()->select([
        'id',
        'inventory_number_current',
        'designation',
        'serial_number',
        'unit_name',
        'service_name',
        'sector_name',
        'sector_description',
        'exact_location',
        'brand_name',
        'model_name',
        'market_label',
        'lot_number',
        'article',
        'date_reception_provisoire',
        'duree_garantie',
        'date_reception_definitive',
        'operational_status',
    ]);
    
    $count = $query->limit(5)->count();
    echo "SUCCESS: Equipment query executed without errors. Returned $count records.\n";
} catch (QueryException $e) {
    echo "QUERY ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
