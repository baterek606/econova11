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
        
        // Find the score badge and add onclick to make it clickable
        $content = preg_replace(
            '/<span style="font-weight: 600; color: #2e7d32; background: #e8f2ec;([^>]+)>(🌱.*?)<\/span>/u',
            '<span onclick="if(<?php echo getUserScore($_SESSION[\'user_id\']); ?> >= 500) openRewardsModal();" style="font-weight: 600; color: #2e7d32; background: #e8f2ec; cursor: pointer; $1">$2</span>',
            $content
        );

        file_put_contents($path, $content);
        echo "Updated clickability for $file\n";
    }
}
?>
