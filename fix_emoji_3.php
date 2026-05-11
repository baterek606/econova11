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
        
        // Use regex to find ?>;">.*?<\?php and replace with ?>;">🌱 <?php
        $content = preg_replace('/(\?>;">).*?(<\?php echo number_format)/s', '$1🌱 $2', $content);
        
        file_put_contents($path, $content);
        echo "Fixed emoji structure 3 for $file\n";
    }
}
?>
