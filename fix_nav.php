<?php
$files = ['index.php', 'explore.php', 'campaigns.php'];
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    $c = str_replace(
        "        </div>\n        <?php if (isset(\$_SESSION['user_id'])): ?>\n        <div id=\"authWrapper\"",
        "        </div>\n        <?php endif; ?>\n        <?php if (isset(\$_SESSION['user_id'])): ?>\n        <div id=\"authWrapper\"",
        $c
    );
    file_put_contents($f, $c);
}

$files2 = ['login.php', 'signup.php'];
foreach ($files2 as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    $c = preg_replace('/<\?php if \(isset\(\$_SESSION\[\'user_id\'\]\)\): \?>\s*<div class="icons">.*?<\/div>\s*<\?php if \(isset\(\$_SESSION\[\'user_id\'\]\)\): \?>/s', "<?php if (isset(\$_SESSION['user_id'])): ?>", $c);
    
    // Also wait, did login.php have a toast between icons and authWrapper?
    // Let me check index.php and login.php properly first to avoid breaking things.
    file_put_contents($f, $c);
}
echo "Fixed";
