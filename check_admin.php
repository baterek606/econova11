<?php
$db = new PDO('sqlite:c:/xampp/htdocs/econova1/econova.db');
$stmt = $db->query("SELECT id, name, email FROM users WHERE name LIKE '%admin%' OR email LIKE '%admin%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
