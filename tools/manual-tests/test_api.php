<?php
require __DIR__.'/../../backend/vendor/autoload.php';
$app = require_once __DIR__.'/../../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/dashboard/kpi/mttr-mtbf/data?start_date=2025-02-28&end_date=2026-02-28&time_unit=hours', 'GET');
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo substr($response->getContent(), 0, 500);
