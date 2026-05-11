<?php
$content = file_get_contents('profile.php');
$content = preg_replace(
    '/<div class="nav-actions">\s*<!--\s*<a href="profile\.php" class="user-profile">.*?<\/a>\s*-->\s*<a href="create_post\.php"/s',
    '<div class="nav-actions">
            <!-- <a href="profile.php" class="user-profile"><img src="user.png" style="width:32px; height:32px; border-radius:50%;"></a> -->
            <span style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px;">🌱 <?php echo number_format(getUserScore($_SESSION[\'user_id\'])); ?> pts</span>
            <a href="create_post.php"',
    $content
);
file_put_contents('profile.php', $content);

$content = file_get_contents('create_post.php');
$content = preg_replace(
    '/<a href="campaign\.php" class="nav-link">Campaigns<\/a>\s*<a href="map\.php" class="nav-link">Map<\/a>/s',
    '<a href="campaign.php" class="nav-link">Campaigns</a>
        <a href="leaderboard.php" class="nav-link">Leaderboard</a>',
    $content
);
$content = preg_replace(
    '/<span style="font-weight: 600; margin-left: 15px; margin-right: 15px; color: var\(--text-main\);">Hi,/s',
    '<span style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px; margin-left: 15px;">🌱 <?php echo number_format(getUserScore($_SESSION[\'user_id\'])); ?> pts</span>
        <span style="font-weight: 600; margin-left: 15px; margin-right: 15px; color: var(--text-main);">Hi,',
    $content
);
file_put_contents('create_post.php', $content);
?>
