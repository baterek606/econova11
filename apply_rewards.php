<?php
$files = [
    'profile.php',
    'leaderboard.php',
    'index.php',
    'explore.php',
    'create_post.php',
    'campaigns.php'
];

$pattern = '/<span style="font-weight: 600; color: #2e7d32; background: #e8f2ec;[^>]+>🌱.*?pts<\/span>/';
$replacement = '<div class="score-dropdown-container">
          $0
          <div class="score-dropdown-content">
            <?php if(getUserScore($_SESSION[\'user_id\']) >= 500): ?>
              <button onclick="openRewardsModal()" class="btn-exchange">Exchange / Redeem</button>
            <?php else: ?>
              <span class="need-points-msg">Need 500 pts to unlock rewards</span>
            <?php endif; ?>
          </div>
        </div>';

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        // Only replace if not already wrapped
        if (strpos($content, 'score-dropdown-container') === false) {
            $new_content = preg_replace($pattern, $replacement, $content);
            if ($new_content !== null) {
                file_put_contents($path, $new_content);
                echo "Updated $file\n";
            }
        } else {
            echo "Already updated $file\n";
        }
    }
}
?>
