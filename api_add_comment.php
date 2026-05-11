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
if (!isset($input['post_id']) || !isset($input['comment_text'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$post_id = (int)$input['post_id'];
$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$comment_text = trim($input['comment_text']);

if (empty($comment_text)) {
    echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, user_name, comment_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $user_name, $comment_text]);

    // Get updated comment count
    $stmt = $db->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'comments_count' => $count]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
