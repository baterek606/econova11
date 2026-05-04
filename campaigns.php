<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$campaigns = [];
try {
    $db = new PDO('sqlite:econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM campaigns";
    if ($category === 'beach' || $category === 'coastal') {
        $sql .= " WHERE title LIKE '%Beach%' OR title LIKE '%Sea%' OR title LIKE '%Coral%' OR location LIKE '%coast%'";
    } elseif ($category === 'urban') {
        $sql .= " WHERE title LIKE '%Urban%' OR location LIKE '%Cairo%'";
    } elseif ($category === 'forest') {
        $sql .= " WHERE title LIKE '%Forest%' OR title LIKE '%Tree%'";
    } elseif ($category === 'river') {
        $sql .= " WHERE title LIKE '%Nile%' OR title LIKE '%River%'";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->query($sql);
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error quietly or log it
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Econova - Active Campaigns</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="campaigns.css">
</head>

<body class="campaigns-page">
  <div class="page-container">

    <!-- Top navigation bar -->
    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>

      <nav class="nav center-nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="campaigns.php" class="nav-link active">Campaigns</a>
        <a href="#" class="nav-link">Map</a>
      </nav>

            <div class="nav-actions">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" placeholder="Search campaigns...">
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

    <main class="campaigns-main">
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
      <section class="campaigns-header">
        <h1 class="page-title">Active Campaigns</h1>
        <p class="page-description">Join a community-led initiative to restore our local ecosystems. From coastal
          cleanups to forest revitalization, every hand matters.</p>
      </section>

      <section class="campaigns-filter-bar">
        <div class="filter-input-wrapper">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          <input type="text" placeholder="Filter by location..." class="location-filter">
        </div>
        <div class="category-filters">
          <a href="?category=all" class="cat-btn <?php echo $category === 'all' ? 'active' : ''; ?>" style="text-decoration:none; display:inline-block; text-align:center;">All Types</a>
          <a href="?category=forest" class="cat-btn <?php echo $category === 'forest' ? 'active' : ''; ?>" style="text-decoration:none; display:inline-block; text-align:center;">#Forest</a>
          <a href="?category=beach" class="cat-btn <?php echo ($category === 'beach' || $category === 'coastal') ? 'active' : ''; ?>" style="text-decoration:none; display:inline-block; text-align:center;">#Beach</a>
          <a href="?category=urban" class="cat-btn <?php echo $category === 'urban' ? 'active' : ''; ?>" style="text-decoration:none; display:inline-block; text-align:center;">#Urban</a>
          <a href="?category=river" class="cat-btn <?php echo $category === 'river' ? 'active' : ''; ?>" style="text-decoration:none; display:inline-block; text-align:center;">#River</a>
        </div>
        <div class="sort-dropdown">
          <span class="sort-label">Sorted by: <strong>Soonest</strong></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="4" y1="6" x2="20" y2="6"></line>
            <line x1="4" y1="12" x2="14" y2="12"></line>
            <line x1="4" y1="18" x2="8" y2="18"></line>
          </svg>
        </div>
      </section>

      <section class="campaigns-grid" id="campaignsGrid">
        <?php if (empty($campaigns)): ?>
            <p style="text-align:center; color:#666; grid-column: 1/-1;">No campaigns found for this category.</p>
        <?php else: ?>
            <?php foreach ($campaigns as $camp): ?>
                <?php 
                $progressLabel = $camp['status'] === 'ACTIVE' ? 'Progress' : 'Capacity';
                $date = date('M d, Y', strtotime($camp['created_at']));
                $stewardsText = $camp['status'] === 'ACTIVE' ? 'Stewardship Partners' : 'Pre-registered';
                ?>
                <div class="campaign-card">
                    <div class="card-image-wrapper">
                        <img src="<?php echo htmlspecialchars($camp['image_url']); ?>" alt="<?php echo htmlspecialchars($camp['title']); ?>" class="card-image">
                        <div class="card-badge <?php echo htmlspecialchars($camp['status']); ?>"><?php echo htmlspecialchars($camp['status']); ?></div>
                    </div>
                    <div class="card-content">
                        <div class="card-date"><?php echo htmlspecialchars($date); ?></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($camp['title']); ?></h3>
                        
                        <div class="card-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span><?php echo htmlspecialchars($camp['location']); ?></span>
                        </div>
                        
                        <div class="card-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <span><?php echo htmlspecialchars($camp['stewards_count']) . ' ' . $stewardsText; ?></span>
                        </div>
                        
                        <div class="card-bottom">
                            <div class="progress-container">
                                <div class="progress-text">
                                    <span><?php echo $progressLabel; ?></span>
                                    <span><?php echo htmlspecialchars($camp['progress_percent']); ?>%</span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: <?php echo htmlspecialchars($camp['progress_percent']); ?>%;"></div>
                                </div>
                            </div>
                            <button class="join-btn" onclick="if(document.querySelector('.btn-join')){ alert('Please login to join'); window.location.href='login.php'; } else { alert('You joined this campaign!'); }">Join</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <section class="newsletter-section">
        <div class="newsletter-content">
          <div class="newsletter-text">
            <h2>Stay rooted in the movement</h2>
            <p>Receive weekly updates on local environmental actions, stewardship tips, and community success stories.
              No spam, just pure growth.</p>
            <div class="newsletter-form">
              <input type="email" placeholder="Enter your email" id="newsletterEmail">
              <button class="btn-subscribe" id="subscribeBtn">Subscribe</button>
            </div>
          </div>
          <div class="newsletter-image">
            <img src="econova_logo.png" alt="Econova Environmental Image">
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
        <p>Econova © 2024. Grounded in community, committed to the earth.</p>
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

  <script src="script.js"></script>
</body>

</html>