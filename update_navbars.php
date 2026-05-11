<?php
$files = ['index.php', 'explore.php', 'campaigns.php', 'profile.php', 'create_post.php', 'map.php', 'db_viewer.php'];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add require_once 'score_helper.php' after session_start();
    if (strpos($content, "require_once 'score_helper.php';") === false) {
        $content = preg_replace('/session_start\(\);\s*\?>/s', "session_start();\nrequire_once 'score_helper.php';\n?>", $content);
        $content = preg_replace('/session_start\(\);\s*\/\//s', "session_start();\nrequire_once 'score_helper.php';\n//", $content);
    }
    
    // Update center-nav to include Leaderboard
    if (strpos($content, 'leaderboard.php') === false) {
        $content = preg_replace('/<a href="campaigns.php" class="nav-link(.*?)">Campaigns<\/a>\s*(<!--\s*<a href="map.php".*?-->)?/s', 
            "<a href=\"campaigns.php\" class=\"nav-link$1\">Campaigns</a>\n        <a href=\"leaderboard.php\" class=\"nav-link\">Leaderboard</a>", $content);
    }
    
    // Update authWrapper to show score
    if (strpos($content, 'getUserScore(') === false) {
        $content = preg_replace('/<span style="font-weight: 600; color: var\(--text-main\);">Welcome/s', 
            '<span style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px;">🌱 <?php echo number_format(getUserScore($_SESSION[\'user_id\'])); ?> pts</span>
          <span style="font-weight: 600; color: var(--text-main);">Welcome', $content);
    }
    
    // In profile.php the layout is a bit different for authWrapper
    if ($file === 'profile.php' && strpos($content, 'getUserScore(') === false) {
        $content = preg_replace('/<div class="nav-actions">\s*<a href="create_post.php"/s', 
            '<div class="nav-actions">
            <span style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px;">🌱 <?php echo number_format(getUserScore($_SESSION[\'user_id\'])); ?> pts</span>
            <a href="create_post.php"', $content);
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
