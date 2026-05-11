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
        // Replace <script src="script.js"></script> with cache busted version
        $content = preg_replace('/<script src="script\.js(?![\?"]).*?><\/script>/', '<script src="script.js?v=<?php echo time(); ?>"></script>', $content);
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
?>
