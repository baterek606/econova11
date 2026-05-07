<?php
try {
    $db = new PDO('sqlite:econova.db');
    $res = $db->query("PRAGMA table_info(campaigns)");
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        echo $row['name'] . " (" . $row['type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
