<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Econova - Home</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="page-container">

    <!-- Top navigation bar -->
    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>

      <nav class="nav center-nav">
        <a href="index.php" class="nav-link active">Home</a>
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="campaigns.php" class="nav-link">Campaigns</a>
        <a href="map.php" class="nav-link">Map</a>
      </nav>

            <div class="nav-actions">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" placeholder="Search stewardship...">
        </div>
        
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
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <span style="font-weight: 600; color: var(--text-main);">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
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

    <main>
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
      <!-- Hero Section -->
      <section class="home-hero">
        <div class="hero-text">
          <div class="badge">
            <span class="dot"></span> Impact Spotlight
          </div>
          <h1 class="hero-title">
            Nurturing Earth, one<br>community at a time.
          </h1>
          <p class="hero-desc">
            Join thousands of stewards transforming their local environments. See the direct impact of collective action
            in real-time.
          </p>
          <div class="hero-actions">
            <a href="signup.php" class="btn btn-dark" style="text-decoration:none;">Start a Campaign &rarr;</a>
            <button class="btn btn-outline" style="border-color: var(--text-green); color: var(--text-green);">View
              Global Map</button>
          </div>
        </div>
        <div class="hero-images">
          <div class="split-image-container" id="splitContainer">
            <img src="after.png" class="img-left" alt="Mangroves After">
            <img src="before.png" class="img-right" id="imgRight" alt="Mangroves Before">
            <div class="split-label left">AFTER</div>
            <div class="split-label right">BEFORE</div>
            <div class="split-slider" id="splitSlider">
              <div class="slider-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="7 13 12 18 17 13"></polyline>
                  <polyline points="7 6 12 11 17 6"></polyline>
                </svg>
              </div>
            </div>
            <input type="range" min="0" max="100" value="50" id="sliderInput"
              style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:col-resize; z-index:10;">
          </div>
          <p class="image-caption">Community Spotlight: Bali Coastline Restoration, August 2024</p>
        </div>
      </section>

      <!-- Live Impact Feed -->
      <section class="feed-section">
        <div class="feed-header">
          <div>
            <h2 class="section-title">Live Impact Feed</h2>
            <p class="section-subtitle">Daily time updates from stewards across the globe</p>
          </div>
          <div class="feed-controls">
            <button class="icon-btn active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
              </svg></button>
            <button class="icon-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg></button>
          </div>
        </div>

        <div class="feed-grid">
          <!-- Column 1: Trending -->
          <div class="feed-col">
            <h3 class="col-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                <polyline points="16 7 22 7 22 13"></polyline>
              </svg> TRENDING NOW</h3>
            <div id="postsContainer"></div>
          </div>

          <!-- Column 2: Active Campaigns -->
          <div class="feed-col">
            <h3 class="col-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg> ACTIVE CAMPAIGNS</h3>
            <div id="campaignsContainer"></div>
          </div>

          <!-- Column 3: Impact & Stewards -->
          <div class="feed-col">
            <h3 class="col-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
              </svg> COMMUNITY IMPACT</h3>
            <div id="statsContainer"></div>
            <div class="card" style="margin-top: 24px;">
              <div class="card-content">
                <p class="title-small">Top Stewards this Month</p>
                <div class="steward-list" id="stewardsContainer"></div>
                <button class="btn btn-outline w-full" style="padding: 8px; font-size: 13px; margin-top: 16px;">View
                  Leaderboard</button>
              </div>
            </div>
          </div>
        </div>
      </section>

    </main>

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
  <script>
    const sliderInput = document.getElementById('sliderInput');
    const splitSlider = document.getElementById('splitSlider');
    const imgRight = document.getElementById('imgRight');

    if (sliderInput) {
      sliderInput.addEventListener('input', (e) => {
        const val = e.target.value;
        splitSlider.style.left = val + '%';
        imgRight.style.clipPath = `inset(0 0 0 ${val}%)`;
      });
    }
  </script>
  <script src="script.js"></script>
</body>

</html>
