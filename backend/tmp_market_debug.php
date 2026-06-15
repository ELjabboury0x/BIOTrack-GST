<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Market;
use Illuminate\Support\Facades\DB;

$marketNumber = '4/2020';
$market = Market::where('market_number', $marketNumber)->first();
if (!$market) {
    echo "No market with market_number {$marketNumber}\n";
    exit(1);
}

echo "market_id={$market->id}\n";
echo "market_number={$market->market_number}\n";
echo "import_lines_count=" . $market->importLines()->count() . "\n";
echo "equipments_count=" . $market->equipments()->count() . "\n";
echo "market_equipment_import_lines direct count=" . DB::table('market_equipment_import_lines')->where('market_id', $market->id)->count() . "\n";
echo "equipments direct count=" . DB::table('equipments')->where('market_id', $market->id)->count() . "\n";
echo "equipments with same market_number via market_label direct count=" . DB::table('equipments')->where('market_label', $market->market_number)->count() . "\n";

echo "sample import line rows:\n";
$lines = DB::table('market_equipment_import_lines')->where('market_id', $market->id)->limit(5)->get();
foreach ($lines as $line) {
    echo "  id={$line->id} designation={$line->designation} market_object={$line->market_object} lot_number={$line->lot_number} article={$line->article} quantity={$line->quantity}\n";
}

echo "sample equipments rows by market_id:\n";
$eqs = DB::table('equipments')->where('market_id', $market->id)->limit(5)->get();
foreach ($eqs as $eq) {
    echo "  id={$eq->id} inventory={$eq->inventory_number_current} designation={$eq->designation} market_label={$eq->market_label}\n";
}

echo "sample equipments rows by market_label {$market->market_number}:\n";
$eqs2 = DB::table('equipments')->where('market_label', $market->market_number)->limit(5)->get();
foreach ($eqs2 as $eq) {
    echo "  id={$eq->id} inventory={$eq->inventory_number_current} designation={$eq->designation} market_label={$eq->market_label} market_id={$eq->market_id}\n";
}
