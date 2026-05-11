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
        
        $content = str_replace('font-size: 14px;""', 'font-size: 14px;"', $content);
        $content = str_replace('margin-left: 15px;""', 'margin-left: 15px;"', $content);
        
        file_put_contents($path, $content);
        echo "Fixed quotes for $file\n";
    }
}
?>
