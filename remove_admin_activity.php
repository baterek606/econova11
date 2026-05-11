<?php
$db = new PDO('sqlite:c:/xampp/htdocs/econova1/econova.db');
$admin_id = 6;

// Delete from post_likes
$stmt1 = $db->prepare("DELETE FROM post_likes WHERE user_id = ?");
$stmt1->execute([$admin_id]);
$likes_deleted = $stmt1->rowCount();

// Delete from user_campaigns
$stmt2 = $db->prepare("DELETE FROM user_campaigns WHERE user_id = ?");
$stmt2->execute([$admin_id]);
$campaigns_deleted = $stmt2->rowCount();

echo "Deleted $likes_deleted likes and $campaigns_deleted campaign joins for admin (ID 6).";
?>
