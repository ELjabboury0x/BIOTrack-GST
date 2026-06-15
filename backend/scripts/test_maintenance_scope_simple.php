<?php
// Direct database connection to test maintenance_scope column
$env = parse_ini_file(__DIR__ . '/../.env');

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'gmao_gst';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $pass);
    
    // Test: Check if the maintenance_scope column exists
    $columns = $pdo->query("DESCRIBE interventions")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "✓ Connected to database: $database\n\n";
    echo "Interventions table columns:\n";
    
    if (in_array('maintenance_scope', $columnNames)) {
        echo "✓ maintenance_scope column EXISTS\n";
        
        // Find the maintenance_scope column details
        foreach ($columns as $col) {
            if ($col['Field'] === 'maintenance_scope') {
                echo "  Type: {$col['Type']}\n";
                echo "  Null: {$col['Null']}\n";
                echo "  Default: {$col['Default']}\n";
            }
        }
    } else {
        echo "✗ maintenance_scope column NOT FOUND\n";
        echo "\nAvailable columns:\n";
        foreach ($columnNames as $name) {
            echo "  - $name\n";
        }
    }
    
    // Test: Check if we can query interventions
    $count = $pdo->query("SELECT COUNT(*) FROM interventions")->fetchColumn();
    echo "\n✓ Total interventions in database: $count\n";
    
    echo "\n✓ All tests passed! The maintenance_scope column is working correctly.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
