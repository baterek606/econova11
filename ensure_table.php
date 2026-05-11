<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('CREATE TABLE IF NOT EXISTS user_stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        trees_planted INTEGER DEFAULT 0,
        plastic_removed_kg REAL DEFAULT 0,
        action_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    echo "user_stats table ensured.\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
