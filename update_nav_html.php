<?php
$files = [
    'profile.php',
    'leaderboard.php',
    'index.php',
    'explore.php',
    'create_post.php',
    'campaigns.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Remove the score-dropdown-container block entirely
        $pattern = '/<div class="score-dropdown-container">.*?<\/div>\s*<\/div>/s';
        $replacement = '<span <?php if(getUserScore($_SESSION[\'user_id\']) >= 500): ?>class="score-badge tooltip-enabled" onclick="openRewardsModal()"<?php else: ?>class="score-badge"<?php endif; ?> style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px; position: relative; cursor: <?php echo (getUserScore($_SESSION[\'user_id\']) >= 500) ? \'pointer\' : \'default\'; ?>;">🌱 <?php echo number_format(getUserScore($_SESSION[\'user_id\'])); ?> pts</span>';
        
        $content = preg_replace($pattern, $replacement, $content);
        
        file_put_contents($path, $content);
        echo "Updated HTML for $file\n";
    }
}
?>
