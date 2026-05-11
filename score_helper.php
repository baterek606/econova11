<?php
function getUserScore($userId) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "SELECT 
                    (
                        (SELECT COUNT(*) FROM user_campaigns WHERE user_id = ?) * 355 + 
                        (SELECT COUNT(*) FROM posts WHERE user_id = ?) * 50 + 
                        IFNULL((SELECT SUM(likes) FROM posts WHERE user_id = ?), 0) * 5
                    ) AS score";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['score'] ? (int)$result['score'] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}
?>
