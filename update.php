<?php
$files = ['index.php', 'explore.php', 'campaigns.php', 'login.php', 'signup.php'];

$navbarSearch = <<<'NAV'
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
NAV;

$navbarReplace = <<<'NAVREP'
        <?php if (isset($_SESSION['user_id'])): ?>
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
NAVREP;

$toastHtml = <<<'TOAST'
      <?php if (isset($_GET['success'])): ?>
      <div id="toast-success" style="position: fixed; top: 20px; right: 20px; background-color: #2e7d32; color: #ffffff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-weight: 500; font-family: 'Inter', sans-serif; z-index: 9999; transition: opacity 0.5s ease-out; opacity: 1;">
          <?php echo htmlspecialchars($_GET['success']); ?>
      </div>
      <script>
          setTimeout(function() {
              var toast = document.getElementById('toast-success');
              if (toast) {
                  toast.style.opacity = '0';
                  setTimeout(function() { toast.remove(); }, 500);
              }
          }, 3000);
      </script>
      <?php endif; ?>
TOAST;

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Replace navbar icons
    $c = str_replace($navbarSearch, $navbarReplace, $c);
    
    if ($f === 'index.php') {
        $c = preg_replace('/<\?php if \(isset\(\$_GET\[\'success\'\]\)\): \?>\s*<div id="toast-success".*?<\?php endif; \?>/s', '', $c);
        $c = preg_replace('/<\?php if \(isset\(\$_GET\[\'success\'\]\)\): \?>\s*<div style="color: #2b6cb0;.*?>\s*<\?php echo htmlspecialchars\(\$_GET\[\'success\'\]\); \?>\s*<\/div>\s*<\?php endif; \?>/s', '', $c);
        $c = str_replace('<main>', "<main>\n" . $toastHtml, $c);
    } elseif ($f === 'explore.php' || $f === 'campaigns.php') {
        $c = preg_replace('/<\?php if \(isset\(\$_GET\[\'success\'\]\)\): \?>\s*<div style="color: #2b6cb0;.*?>\s*<\?php echo htmlspecialchars\(\$_GET\[\'success\'\]\); \?>\s*<\/div>\s*<\?php endif; \?>/s', $toastHtml, $c);
    } elseif ($f === 'login.php' || $f === 'signup.php') {
        $c = preg_replace('/<\?php elseif \(isset\(\$_GET\[\'success\'\]\)\): \?>\s*<div id="success-message".*?<\/div>/s', '', $c);
        $c = preg_replace('/<\?php if \(isset\(\$_GET\[\'success\'\]\)\): \?>\s*<div id="toast-success".*?<\?php endif; \?>/s', '', $c);
        $c = str_replace('<div class="page-container">', "<div class=\"page-container\">\n" . $toastHtml, $c);
    }
    
    file_put_contents($f, $c);
}
echo "Done";
