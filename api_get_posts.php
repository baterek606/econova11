<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT * FROM posts ORDER BY id DESC LIMIT 5");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$p) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?");
        $stmt->execute([$p['id']]);
        $p['likes_count'] = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id = ?");
        $stmt->execute([$p['id']]);
        $p['comments_count'] = $stmt->fetchColumn();
        
        $p['user_liked'] = false;
        if (isset($_SESSION['user_id'])) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$p['id'], $_SESSION['user_id']]);
            $p['user_liked'] = $stmt->fetchColumn() > 0;
        }

        // Add dummy data expected by script.js
        $p['type'] = 'REFORESTATION';
        $p['author_name'] = $p['user_name'];
        $p['time_ago'] = 'Just now';
        $p['content'] = $p['description'];
    }

    echo json_encode($posts);
} catch(PDOException $e) {
    echo json_encode([]);
}
?>
