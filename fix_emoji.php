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
        
        $content = preg_replace('/\?\?\?/u', '🌱', $content);
        
        file_put_contents($path, $content);
        echo "Fixed emoji for $file\n";
    }
}
?>
