<?php
$files = [
    'profile.php',
    'leaderboard.php',
    'index.php',
    'explore.php',
    'create_post.php',
    'campaigns.php',
    'login.php',
    'signup.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Cache bust style.css
        $content = preg_replace('/href="style\.css(?![\?"]).*?"/', 'href="style.css?v=<?php echo time(); ?>"', $content);
        
        file_put_contents($path, $content);
        echo "Cache-busted style.css for $file\n";
    }
}
?>
