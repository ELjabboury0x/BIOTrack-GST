<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$columns = DB::getConnection()->getDoctrineSchemaManager()->listTableColumns('interventions');
foreach($columns as $col) {
    echo $col->getName() . ' - ' . $col->getType()->getName() . "\n";
}
?>
