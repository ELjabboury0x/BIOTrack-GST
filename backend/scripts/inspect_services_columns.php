<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=gmao_gst','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SHOW COLUMNS FROM services');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' | ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
