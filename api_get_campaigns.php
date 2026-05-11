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
    $db = new PDO('sqlite:econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Support optional category filtering if needed
    $category = isset($_GET['category']) ? $_GET['category'] : 'all';
    
    // We could add category logic here if campaigns table had a category column.
    // For now we just return all active campaigns.
    
    $stmt = $db->query("SELECT * FROM campaigns ORDER BY created_at DESC");
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the date if needed, matching frontend expected format
    foreach ($campaigns as &$camp) {
        $camp['date'] = date('M d, Y', strtotime($camp['created_at']));
        if (!isset($camp['engagement_count']) && isset($camp['stewards_count'])) {
            $camp['engagement_count'] = $camp['stewards_count'];
        } else if (isset($camp['engagement_count']) && !isset($camp['stewards_count'])) {
            $camp['stewards_count'] = $camp['engagement_count'];
        }
    }

    echo json_encode($campaigns);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
