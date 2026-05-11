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
        
        // Fix the corrupted emoji before the PHP echo
        $content = preg_replace('/<span onclick="[^>]+>\s*.*?<\?php echo number_format/s', '<span onclick="if(<?php echo getUserScore($_SESSION[\'user_id\']); ?> >= 500) openRewardsModal();" style="font-weight: 600; color: #2e7d32; background: #e8f2ec; cursor: pointer; padding: 4px 12px; border-radius: 20px; font-size: 14px;">🌱 <?php echo number_format', $content);
        
        file_put_contents($path, $content);
        echo "Fixed emoji structure for $file\n";
    }
}
?>
