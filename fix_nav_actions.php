<?php
$files = ['index.php', 'explore.php', 'campaigns.php'];
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // First, let's clean up index.php which got duplicated
    if ($f === 'index.php') {
        // Find the second <!DOCTYPE html> and remove everything before it
        $pos = strrpos($c, '<!DOCTYPE html>');
        if ($pos !== false && $pos > 100) {
            $c = substr($c, $pos);
            // Re-add the session start block at the top
            $sessionBlock = "<?php\nsession_set_cookie_params([\n    'lifetime' => 86400 * 30,\n    'path' => '/',\n    'secure' => false,\n    'httponly' => true\n]);\nsession_start();\n?>\n";
            $c = $sessionBlock . $c;
        }
    }
    
    // Now replace the nav-actions safely
    $navReplacement = <<<HTML
      <div class="nav-actions">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" placeholder="Search stewardship...">
        </div>
        
        <?php if (isset(\$_SESSION['user_id'])): ?>
        <div class="icons">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
          </svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <span style="font-weight: 600; color: var(--text-main);">Welcome, <?php echo htmlspecialchars(\$_SESSION['user_name']); ?></span>
          <a href="api_logout.php" class="btn btn-outline btn-sm" style="text-decoration:none;">Logout</a>
        </div>
        <?php else: ?>
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <a href="login.php" class="login-link">Login</a>
          <a href="signup.php" class="btn-join">Join Us</a>
        </div>
        <?php endif; ?>
      </div>
    </header>
HTML;

    // Use regex to replace the entire nav-actions block
    $c = preg_replace('/<div class="nav-actions">.*?<\/header>/s', $navReplacement, $c);
    
    file_put_contents($f, $c);
}
echo "Done";
