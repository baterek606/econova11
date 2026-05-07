<?php
try {
    $db = new SQLite3('econova.db');
    $res = $db->query("PRAGMA table_info(campaigns)");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        echo $row['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
