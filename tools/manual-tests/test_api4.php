<?php
require __DIR__.'/../../backend/vendor/autoload.php';
$app = require_once __DIR__.'/../../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $controller = app(\App\Http\Controllers\MttrMtbfController::class);
    $request = Illuminate\Http\Request::create('/data', 'GET', ['start_date'=>'2025-02-28', 'end_date'=>'2026-02-28']);
    var_dump($controller->data($request)->getContent());
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}

