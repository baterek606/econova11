<?php
$db = new PDO('sqlite:c:/xampp/htdocs/econova1/econova.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// check if user_id exists in posts
$stmt = $db->query('PRAGMA table_info(posts)');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
$has_user_id = false;
foreach($cols as $c) {
    if ($c['name'] === 'user_id') $has_user_id = true;
}

if (!$has_user_id) {
    $db->exec("ALTER TABLE posts ADD COLUMN user_id INTEGER");
    echo "Added user_id column to posts table.";
} else {
    echo "user_id column already exists in posts table.";
}
?>
