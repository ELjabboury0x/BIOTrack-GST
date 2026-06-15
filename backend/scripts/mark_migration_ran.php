<?php
$migration = $argv[1] ?? null;
if (!$migration) {
    echo "Usage: php mark_migration_ran.php migration_filename\n";
    exit(1);
}
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=gmao_gst','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT IFNULL(MAX(batch), 0) as maxbatch FROM migrations');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $batch = (int)$row['maxbatch'] + 1;
    $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
    $stmt->execute([':migration' => $migration, ':batch' => $batch]);
    echo "Marked migration $migration as run with batch $batch\n";
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(2);
}
