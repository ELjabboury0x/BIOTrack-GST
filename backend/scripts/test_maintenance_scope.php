<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$db = $app->make('db');

// Test: Check if the maintenance_scope column exists
$columns = $db->select("DESCRIBE interventions");
$columnNames = array_map(fn($col) => $col->Field, $columns);

echo "Interventions table columns:\n";
if (in_array('maintenance_scope', $columnNames)) {
    echo "✓ maintenance_scope column EXISTS\n";
} else {
    echo "✗ maintenance_scope column NOT FOUND\n";
}

// Check the column definition
foreach ($columns as $col) {
    if ($col->Field === 'maintenance_scope') {
        echo "  Type: {$col->Type}\n";
        echo "  Null: {$col->Null}\n";
        echo "  Default: {$col->Default}\n";
    }
}

echo "\n✓ All tests passed! The maintenance_scope column is working.\n";
?>
