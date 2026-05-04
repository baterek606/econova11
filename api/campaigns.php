<?php
require_once '../db_connect.php';
require_once '../auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM campaigns WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $campaign = $stmt->fetch();
            echo json_encode($campaign);
        } else {
            $stmt = $pdo->query('SELECT * FROM campaigns ORDER BY created_at DESC');
            $campaigns = $stmt->fetchAll();
            echo json_encode($campaigns);
        }
        break;

    case 'POST':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST; // Fallback for standard form data

        $sql = "INSERT INTO campaigns (title, description, location, goal, latitude, longitude, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['location'],
            $data['goal'],
            $data['latitude'],
            $data['longitude'],
            $data['image'] ?? 'forest.png'
        ]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'DELETE':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('DELETE FROM campaigns WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            echo json_encode(['success' => true]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>
