<?php
// Test MTTR view rendering
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

try {
    // Create a test request
    $request = \Illuminate\Http\Request::create('/dashboard/kpi/mttr-mtbf', 'GET');
    
    // Handle the request
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Content Length: " . strlen($response->getContent()) . "\n";
    echo "First 500 chars:\n";
    echo substr($response->getContent(), 0, 500);
    echo "\n\nBody check:\n";
    $content = $response->getContent();
    $bodyStart = strpos($content, '<body');
    $bodyEnd = strpos($content, '</body>');
    if ($bodyStart !== false && $bodyEnd !== false) {
        echo "Body found at position $bodyStart\n";
        echo "Body content length: " . ($bodyEnd - $bodyStart) . "\n";
        echo "Body content preview: " . substr($content, $bodyStart, min(200, $bodyEnd - $bodyStart)) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
?>
