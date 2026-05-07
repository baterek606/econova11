<?php
header('Content-Type: text/plain');
try {
    $db = new PDO('sqlite:econova.db');
    $stmt = $db->query('PRAGMA table_info(campaigns)');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
