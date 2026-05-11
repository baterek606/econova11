<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

$post_id = (int)$input['post_id'];
$user_id = (int)$_SESSION['user_id'];

try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user has already liked the post
    $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $liked = $stmt->fetchColumn() > 0;

    if ($liked) {
        // Unlike
        $stmt = $db->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $newStatus = false;
    } else {
        // Like
        $stmt = $db->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);
        $newStatus = true;
    }

    // Get the updated like count
    $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'liked' => $newStatus, 'likes_count' => $count]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
