<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../bootstrap/app.php';

use App\Models\Equipment;

// Test 1: Count equipment with company_id = NULL
$countNull = Equipment::query()->whereNull('company_id')->count();
echo "Equipment with company_id = NULL: $countNull\n";

// Test 2: Count all equipment
$countAll = Equipment::query()->count();
echo "All equipment: $countAll\n";

// Test 3: Sample query with whereNull
$sample = Equipment::query()
    ->whereNull('company_id')
    ->limit(5)
    ->pluck('id', 'company_id');
echo "Sample (first 5 with company_id=NULL):\n";
dd($sample);
