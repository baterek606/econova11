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

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['campaign_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$campaign_id = (int)$data['campaign_id'];
$user_id = $_SESSION['user_id'];

try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS user_campaigns (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        campaign_id INTEGER,
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, campaign_id)
    )");

    // Try to insert
    $stmt = $db->prepare("INSERT OR IGNORE INTO user_campaigns (user_id, campaign_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $campaign_id]);
    
    $rowsAffected = $stmt->rowCount();
    
    // Also update engagement_count in campaigns table
    if ($rowsAffected > 0) {
        $updateStmt = $db->prepare("UPDATE campaigns SET engagement_count = COALESCE(engagement_count, 0) + 1 WHERE id = ?");
        $updateStmt->execute([$campaign_id]);
    }

    echo json_encode([
        'success' => true,
        'new_join' => ($rowsAffected > 0),
        'message' => ($rowsAffected > 0) ? 'Successfully joined campaign!' : 'You have already joined this campaign.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
