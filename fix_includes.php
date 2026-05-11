<?php
$files = ['index.php', 'explore.php', 'campaigns.php', 'profile.php', 'create_post.php', 'map.php', 'leaderboard.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, "require_once 'functions.php';") === false) {
            // First check if there is score_helper.php and replace it, otherwise insert it after session_start();
            if (strpos($content, "require_once 'score_helper.php';") !== false) {
                $content = str_replace("require_once 'score_helper.php';", "require_once 'functions.php';", $content);
            } else {
                $content = preg_replace('/session_start\(\);\s*/', "session_start();\nrequire_once 'functions.php';\n", $content, 1);
            }
            file_put_contents($file, $content);
            echo "Updated $file\n";
        }
    }
}
?>
