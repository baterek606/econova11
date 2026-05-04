<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Econova - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login_page.css">
</head>
<body>
  <div class="page-container">

      <!-- FLOATING SUCCESS TOAST -->
      <?php if (isset($_GET['success'])): ?>
      <div id="toast-success" style="position: fixed; top: 20px; right: 20px; background-color: #2e7d32; color: #ffffff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-weight: 500; font-family: 'Inter', sans-serif; z-index: 9999; transition: opacity 0.5s ease-out; opacity: 1;">
          <?php echo htmlspecialchars($_GET['success']); ?>
      </div>
      <script>
          setTimeout(function() {
              var toast = document.getElementById('toast-success');
              if (toast) {
                  toast.style.opacity = '0'; // Smooth fade out
                  setTimeout(function() { toast.remove(); }, 500); // Remove from DOM after fade
              }
          }, 3000); // Wait 3 seconds
      </script>
      <?php endif; ?>

    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>
      <nav class="nav center-nav">
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="campaigns.php" class="nav-link">Campaigns</a>
        <a href="#" class="nav-link">Map</a>
      </nav>
      <div class="nav-actions">
        <!-- New conditional Auth UI goes here! -->
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <a href="login.php" class="login-link">Login</a>
          <a href="signup.php" class="btn-join">Join Us</a>
        </div>
      </div>
    </header>

    <main class="container" >
        <!-- Left side info omitted for brevity -->
        <section class="left-panel">
            <div class="left-content">
                <div class="tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L4 20H20L12 2Z" fill="currentColor"/>
                    </svg>
                    GROUNDED IN COMMUNITY
                </div>
                <h1>Cultivating a greener future, together.</h1>
                <p>Join our community of sustainability advocates and start your stewardship journey today.</p>
            </div>
        </section>
        <section class="right-panel">
            <div class="form-container">
                <div class="brand-large">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        <path d="M12 6c-3.31 0-6 2.69-6 6 0 2.21 1.2 4.14 3 5.19V18h6v-0.81c1.8-1.05 3-2.98 3-5.19 0-3.31-2.69-6-6-6z"/>
                    </svg>
                    <span>Econova</span>
                </div>
                <h2>Welcome back</h2>
                <p class="subtitle">Please enter your details to access your account.</p>

                <form action="api_login.php" method="POST">
                    
                    <!-- Red Error Messages stay nicely embedded in the form as requested -->
                    <?php if (isset($_GET['error'])): ?>
                        <div id="error-message" style="color: #e53e3e; background-color: #fff5f5; border-left: 4px solid #fc8181; padding: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-radius: 4px;">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <label for="email">EMAIL ADDRESS</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" id="email" placeholder="hello@econova.org" required>
                            <span class="icon">@</span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">PASSWORD</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Login</button>
                </form>

                <div class="footer-text">
                    New to the community? <a href="signup.php">Join Us</a>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo">Econova</div>
          <p>Nurturing stewardship for a sustainable future. Empowering communities to take ownership of their local
            environment through collective action.</p>
          <div class="socials">
            <span>🌐</span><span>✉️</span><span>🔗</span>
          </div>
        </div>
        <div>
          <h4>RESOURCES</h4>
          <a href="#">Community</a>
          <a href="#">Guidelines</a>
          <a href="#">Environmental Policy</a>
        </div>
        <div>
          <h4>SUPPORT</h4>
          <a href="#">Contact</a>
          <a href="#">FAQ</a>
          <a href="#">Help Center</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2024 Econova. Nurturing stewardship for a sustainable future.</p>
        <div class="legal">
          <a href="#">Privacy</a>
          <a href="#">Terms</a>
          <a href="#">Cookies</a>
        </div>
      </div>
    </footer>
  </div>
</body>
</html>
