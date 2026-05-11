<?php
function getUserScore($userId) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $score = 0;

        // 1. Calculate Campaigns Joined (100 points per unique campaign)
        try {
            // Use DISTINCT campaign_id to prevent double counting if a user joins multiple times
            $stmt = $db->prepare("SELECT COUNT(DISTINCT campaign_id) FROM user_campaigns WHERE user_id = ?");
            $stmt->execute([$userId]);
            $campaigns_joined = (int)$stmt->fetchColumn();
            $score += $campaigns_joined * 100;
        } catch (PDOException $e) {
            // Table might not exist yet, ignore and continue
        }

        // 2. Calculate Trees Planted and Plastic Removed from user_stats
        try {
            // Querying a dedicated user_stats table 
            $stmt = $db->prepare("SELECT SUM(trees_planted) as total_trees, SUM(plastic_removed_kg) as total_plastic FROM user_stats WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats) {
                $trees_planted = (int)$stats['total_trees'];
                $plastic_removed = (float)$stats['total_plastic'];
                
                $score += $trees_planted * 50;
                $score += $plastic_removed * 10;
            }
        } catch (PDOException $e) {
            // Table might not exist yet, ignore and continue
        }

        return (int)$score;
    } catch (PDOException $e) {
        return 0; // Return 0 if main database connection error occurs
    }
}
?>
