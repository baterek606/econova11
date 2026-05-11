<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS post_likes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER,
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS post_comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER,
        user_id INTEGER,
        user_name TEXT,
        comment_text TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Tables post_likes and post_comments created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
