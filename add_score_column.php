<?php
require_once 'score_helper.php';

try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add column if it doesn't exist
    try {
        $db->exec("ALTER TABLE users ADD COLUMN score INTEGER DEFAULT 0");
        echo "Added 'score' column to users table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column name') !== false) {
            echo "Column 'score' already exists.\n";
        } else {
            echo "Error adding column: " . $e->getMessage() . "\n";
        }
    }

    // Update existing users
    $stmt = $db->query("SELECT id FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $db->prepare("UPDATE users SET score = ? WHERE id = ?");
    
    foreach ($users as $user) {
        $score = getUserScore($user['id']);
        $updateStmt->execute([$score, $user['id']]);
        echo "Updated user " . $user['id'] . " with score " . $score . "\n";
    }
    
    echo "Done updating scores.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
