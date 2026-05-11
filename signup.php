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
    <title>Econova - Create Account</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Link to global styles -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Signup Specific Styles */
        .auth-main {
            display: flex;
            min-height: 90vh;
            margin-bottom: 40px; /* Added spacing above footer */
        }

        .auth-left-panel {
            flex: 1;
            background-color: #0a2e1f;
            /* Added dark green gradient overlay to ensure text is readable on forest.png */
            background-image: linear-gradient(to top, rgba(10, 46, 31, 0.9) 0%, rgba(10, 46, 31, 0.4) 100%), url('redwood_basin.png');
            background-size: cover;
            background-position: center;
            position: relative;
            color: #ffffff;
            display: flex;
            align-items: flex-end;
            padding: 4rem;
        }

        .auth-left-content {
            position: relative;
            z-index: 2;
            max-width: 450px;
        }

        .auth-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }

        .auth-left-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .auth-left-content p {
            font-size: 1.125rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .auth-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .auth-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .auth-right-panel {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem;
        }

        .auth-form-container {
            width: 100%;
            max-width: 400px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1a2620;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1a2620;
        }

        .auth-subtitle {
            color: #6e7672;
            margin-bottom: 2.5rem;
        }

        .auth-input-group {
            margin-bottom: 1.5rem;
        }

        .auth-input-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            color: #1a2620;
            text-transform: uppercase;
        }

        .auth-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .auth-input-wrapper input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e8e4db;
            border-radius: 0.5rem;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s ease;
            font-family: inherit;
        }

        .auth-input-wrapper input:focus {
            border-color: #5a7964;
        }

        .auth-checkbox-container {
            display: flex;
            align-items: center;
            position: relative;
            cursor: pointer;
            user-select: none;
            color: #6e7672;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .auth-checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .auth-checkmark {
            height: 18px;
            width: 18px;
            background-color: transparent;
            border: 1px solid #e8e4db;
            border-radius: 4px;
            margin-right: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-checkbox-container input:checked ~ .auth-checkmark {
            background-color: #5a7964;
            border-color: #5a7964;
        }

        .auth-checkbox-container input:checked ~ .auth-checkmark:after {
            content: "";
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .auth-submit-btn {
            width: 100%;
            background-color: #1a2620;
            color: #ffffff;
            border: none;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-bottom: 2rem;
            font-family: inherit;
        }

        .auth-submit-btn:hover {
            background-color: #000;
        }

        .auth-footer-text {
            text-align: center;
            color: #6e7672;
            font-size: 0.875rem;
        }

        .auth-footer-text a {
            color: #5a7964;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .auth-main {
                flex-direction: column;
            }
            .auth-left-panel {
                padding: 6rem 2rem 3rem;
                min-height: 50vh;
            }
            .auth-right-panel {
                padding: 3rem 2rem;
            }
        }
    </style>
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
                  toast.style.opacity = '0';
                  setTimeout(function() { toast.remove(); }, 500);
              }
          }, 3000);
      </script>
      <?php endif; ?>

    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>
      <nav class="nav center-nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="campaigns.php" class="nav-link">Campaigns</a>
        <!-- <a href="#" class="nav-link">Map</a> -->
      </nav>
      <div class="nav-actions">
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <a href="login.php" class="login-link">Login</a>
          <a href="signup.php" class="btn-join">Join Us</a>
        </div>
      </div>
    </header>

    <main class="auth-main">
        <!-- Left Panel -->
        <section class="auth-left-panel">
            <div class="auth-left-content">
                <div class="auth-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L4 20H20L12 2Z" fill="currentColor"/>
                    </svg>
                    ROOTED IN CHANGE
                </div>
                <h1>Nurture your impact with Econova.</h1>
                <p>Join a global movement of environmental stewards dedicated to preserving and restoring our planet's ecosystems.</p>
                <div class="auth-features">
                    <div class="auth-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Community Power
                    </div>
                    <div class="auth-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Traceable Impact
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Panel (Form) -->
        <section class="auth-right-panel">
            <div class="auth-form-container">
                <div class="auth-brand">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        <path d="M12 6c-3.31 0-6 2.69-6 6 0 2.21 1.2 4.14 3 5.19V18h6v-0.81c1.8-1.05 3-2.98 3-5.19 0-3.31-2.69-6-6-6z"/>
                    </svg>
                    <span>Econova</span>
                </div>
                <h2 class="auth-title">Create your account</h2>
                <p class="auth-subtitle">Start your stewardship journey today.</p>

                <form action="api_signup.php" method="POST">
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div id="error-message" style="color: #e53e3e; background-color: #fff5f5; border-left: 4px solid #fc8181; padding: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; border-radius: 4px;">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="auth-input-group">
                        <label for="name">FULL NAME</label>
                        <div class="auth-input-wrapper">
                            <input type="text" name="name" id="name" placeholder="Alex Rivers" required>
                        </div>
                    </div>

                    <div class="auth-input-group">
                        <label for="email">EMAIL ADDRESS</label>
                        <div class="auth-input-wrapper">
                            <input type="email" name="email" id="email" placeholder="alex@econova.eco" required>
                        </div>
                    </div>

                    <div class="auth-input-group">
                        <label for="password">PASSWORD</label>
                        <div class="auth-input-wrapper">
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <label class="auth-checkbox-container">
                        <input type="checkbox" required>
                        <span class="auth-checkmark"></span>
                        I agree to the Terms of Service and Privacy Policy
                    </label>

                    <button type="submit" class="auth-submit-btn">
                        Create My Account
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>

                <div class="auth-footer-text">
                    Already have an account? <a href="login.php">Sign In</a>
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
          <?php if(isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@econova.com'): ?>
            <a href="db_viewer.php" style="color: var(--text-green); font-weight: 600;">DB Viewer</a>
          <?php endif; ?>
        </div>
      </div>
    </footer>

  </div>
</body>
</html>
