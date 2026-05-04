<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Mock Goal Data
$goalTitle = "Plant 500,000 Trees in Urban Areas";
$goalTarget = 500000;
$goalCurrent = 342150;
$goalPercent = round(($goalCurrent / $goalTarget) * 100);

// Fetch Explore Posts Data from SQLite Database
try {
    $db = new PDO('sqlite:' . __DIR__ . '/econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check and create posts table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_name TEXT,
        location TEXT,
        title TEXT,
        description TEXT,
        category TEXT,
        likes_count INTEGER DEFAULT 0,
        comments_count INTEGER DEFAULT 0,
        before_image TEXT,
        after_image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 2. Fetch posts based on category filter
    if ($category === 'all') {
        $stmt = $db->query("SELECT * FROM posts ORDER BY id ASC");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->prepare("SELECT * FROM posts WHERE category = :category ORDER BY id ASC");
        $stmt->execute([':category' => $category]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    // If table doesn't exist or error occurs
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Econova - Explore Feed</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="explore.css">
</head>

<body>
  <div class="page-container">

    <!-- Top navigation bar -->
    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>

      <nav class="nav center-nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="explore.php" class="nav-link active">Explore</a>
        <a href="campaigns.php" class="nav-link">Campaigns</a>
        <a href="#" class="nav-link">Map</a>
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

    <main class="explore-container">
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

      <!-- Left Sidebar -->
      <aside class="sidebar">
        <div class="filter-section">
          <h3 class="filter-title"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg> Ecosystems</h3>
          <ul class="filter-list" id="filterList">
            <li><a href="?category=all" class="filter-btn <?php echo $category === 'all' ? 'active' : ''; ?>" style="text-decoration:none; display:flex; align-items:center;"><svg width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z">
                  </path>
                  <path
                    d="M12 6c-3.31 0-6 2.69-6 6 0 2.21 1.2 4.14 3 5.19V18h6v-0.81c1.8-1.05 3-2.98 3-5.19 0-3.31-2.69-6-6-6z">
                  </path>
                </svg> All Impact</a></li>
            <li><a href="?category=forest" class="filter-btn <?php echo $category === 'forest' ? 'active' : ''; ?>" style="text-decoration:none; display:flex; align-items:center;"><svg width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2L3 14h6v8h6v-8h6L12 2z"></path>
                </svg> #Forest</a></li>
            <li><a href="?category=beach" class="filter-btn <?php echo $category === 'beach' ? 'active' : ''; ?>" style="text-decoration:none; display:flex; align-items:center;"><svg width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg> #Beach</a></li>
            <li><a href="?category=urban" class="filter-btn <?php echo $category === 'urban' ? 'active' : ''; ?>" style="text-decoration:none; display:flex; align-items:center;"><svg width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                  <path d="M9 22v-4h6v4"></path>
                  <path d="M8 6h.01"></path>
                  <path d="M16 6h.01"></path>
                  <path d="M12 6h.01"></path>
                  <path d="M12 10h.01"></path>
                  <path d="M12 14h.01"></path>
                  <path d="M16 10h.01"></path>
                  <path d="M16 14h.01"></path>
                  <path d="M8 10h.01"></path>
                  <path d="M8 14h.01"></path>
                </svg> #Urban</a></li>
            <li><a href="?category=river" class="filter-btn <?php echo $category === 'river' ? 'active' : ''; ?>" style="text-decoration:none; display:flex; align-items:center;"><svg width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg> #River</a></li>
          </ul>
        </div>

        <div class="card goal-card" id="goalContainer">
          <div class="card-content">
            <p class="title-small" style="margin-bottom: 8px;">Community Goal</p>
            <p class="goal-desc" id="goalTitle" style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
              <?php echo htmlspecialchars($goalTitle); ?></p>
            <div class="progress-section" style="margin-top: 16px; margin-bottom: 0;">
              <div class="progress-bar"
                style="background-color: #E8F2EC; height: 6px; border-radius: 3px; margin-bottom: 8px;">
                <div class="progress-fill" id="goalProgress"
                  style="width: <?php echo $goalPercent; ?>%; border-radius: 3px; background-color: var(--text-green);"></div>
              </div>
              <div class="progress-labels" style="margin-top: 8px;">
                <strong id="goalCurrent" style="font-size: 10px; font-weight: 700;"><?php echo number_format($goalCurrent); ?> planted</strong>
                <strong class="text-green" id="goalPercent" style="font-size: 10px; font-weight: 700;"><?php echo $goalPercent; ?>%</strong>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Right Main Content -->
      <section class="explore-content">
        <div class="feed-header">
          <div>
            <h2 class="section-title">Environmental Stewardship</h2>
            <p class="section-subtitle">Real impact stories from our community worldwide.</p>
          </div>
          <div class="feed-controls">
            <button class="icon-btn active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg></button>
            <button class="icon-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
              </svg></button>
          </div>
        </div>

        <div class="explore-grid" id="explorePostsContainer">
          <?php if (empty($posts)): ?>
            <p style="color: var(--text-muted); grid-column: 1/-1; text-align: center;">No impact stories in this category yet.</p>
          <?php else: ?>
            <?php foreach ($posts as $p): ?>
              <div class="card explore-card">
                <div class="split-image-container" style="position:relative; width:100%; height:300px; overflow:hidden; border-radius: 12px 12px 0 0;">
                  <?php
                    $emojiMap = [
                        'beach' => '🏖️',
                        'forest' => '🌲',
                        'urban' => '🏙️',
                        'river' => '🌊'
                    ];
                    $cat = strtolower($p['category']);
                    $emoji = isset($emojiMap[$cat]) ? $emojiMap[$cat] : '🌍';

                    $beforeIsLocal = (empty($p['before_image']) || $p['before_image'] === 'local');
                    $afterIsLocal = (empty($p['after_image']) || $p['after_image'] === 'local');
                  ?>

                  <!-- After Image (Left Side logically, underneath) -->
                  <?php if ($afterIsLocal): ?>
                    <div class="img-left placeholder-bg" style="position:absolute; width:100%; height:100%; display:flex; align-items:center; justify-content:center; background-color:#eef2f0; font-size:64px; margin:0;">
                      <?php echo $emoji; ?>
                    </div>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars($p['after_image']); ?>" class="img-left" alt="After" style="position:absolute; width:100%; height:100%; object-fit:cover;">
                  <?php endif; ?>

                  <!-- Before Image (Right Side logically, overlaid with clip-path) -->
                  <?php if ($beforeIsLocal): ?>
                    <div class="img-right placeholder-bg" id="exploreImgRight-<?php echo $p['id']; ?>" style="position:absolute; width:100%; height:100%; display:flex; align-items:center; justify-content:center; background-color:#d0d9d4; font-size:64px; margin:0; clip-path:inset(0 0 0 50%); filter: grayscale(100%);">
                      <?php echo $emoji; ?>
                    </div>
                  <?php else: ?>
                    <img src="<?php echo htmlspecialchars($p['before_image']); ?>" class="img-right" id="exploreImgRight-<?php echo $p['id']; ?>" alt="Before" style="position:absolute; width:100%; height:100%; object-fit:cover; clip-path:inset(0 0 0 50%);">
                  <?php endif; ?>

                  <div class="split-label left" style="position:absolute; bottom:16px; left:16px; background:rgba(0,0,0,0.6); color:white; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">AFTER</div>
                  <div class="split-label right" style="position:absolute; bottom:16px; right:16px; background:rgba(0,0,0,0.6); color:white; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">BEFORE</div>

                  <div class="split-slider" id="exploreSplitSlider-<?php echo $p['id']; ?>" style="position:absolute; top:0; bottom:0; left:50%; width:4px; background:white; transform:translateX(-50%); pointer-events:none;">
                    <div class="slider-btn" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:32px; height:32px; background:white; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.3);">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>
                    </div>
                  </div>
                  <input type="range" min="0" max="100" value="50" class="explore-slider-input" data-id="<?php echo $p['id']; ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:col-resize; z-index:10;" oninput="
                    const val = this.value;
                    const id = this.dataset.id;
                    document.getElementById('exploreSplitSlider-' + id).style.left = val + '%';
                    document.getElementById('exploreImgRight-' + id).style.clipPath = 'inset(0 0 0 ' + val + '%)';
                  ">
                </div>

                <div class="card-content">
                  <div class="user-meta" style="margin-bottom: 12px;">
                    <div class="avatar" style="background-image: url('https://i.pravatar.cc/100?u=<?php echo urlencode($p['user_name']); ?>');"></div>
                    <div>
                      <strong><?php echo htmlspecialchars($p['user_name']); ?></strong>
                      <span class="time"><?php echo htmlspecialchars($p['location']); ?></span>
                    </div>
                  </div>

                  <h4><?php echo htmlspecialchars($p['title']); ?></h4>
                  <p><?php echo htmlspecialchars($p['description']); ?></p>

                  <div class="feed-actions">
                    <div class="stats">
                      <?php 
                        $onclickLike = "if(document.querySelector('.btn-join')){ alert('Please login to like'); window.location.href='login.php'; } else { likeExplorePost(" . $p['id'] . "); }";
                        $onclickComment = "if(document.querySelector('.btn-join')){ alert('Please login to comment'); window.location.href='login.php'; } else { commentExplorePost(" . $p['id'] . "); }";
                      ?>
                      <span style="cursor:pointer" onclick="<?php echo $onclickLike; ?>">♡ <span id="like-count-<?php echo $p['id']; ?>" data-raw="<?php echo $p['likes_count']; ?>"><?php echo $p['likes_count'] >= 1000 ? number_format($p['likes_count']/1000, 1).'k' : $p['likes_count']; ?></span></span>
                      <span style="cursor:pointer" onclick="<?php echo $onclickComment; ?>">💬 <span id="comment-count-<?php echo $p['id']; ?>" data-raw="<?php echo $p['comments_count']; ?>"><?php echo $p['comments_count'] >= 1000 ? number_format($p['comments_count']/1000, 1).'k' : $p['comments_count']; ?></span></span>
                    </div>
                    <span class="badge small" style="margin: 0; background-color: var(--bg-badge); color: var(--text-green);">#<?php echo strtoupper(htmlspecialchars($p['category'])); ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 48px; margin-bottom: 48px;">
          <button class="btn btn-outline"
            style="padding: 12px 32px; font-weight: 600; border-color: var(--text-main); color: var(--text-main);">View
            More Impact Stories</button>
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

  <script src="script.js"></script>
  <script>
    window.toggleSlider = function(postId, target) {
      const imgBefore = document.getElementById(`img-before-${postId}`);
      const imgAfter = document.getElementById(`img-after-${postId}`);
      const badge = document.getElementById(`badge-${postId}`);

      if (target === 'after') {
        imgBefore.classList.add('hidden');
        imgAfter.classList.remove('hidden');
        badge.textContent = 'AFTER';
      } else {
        imgAfter.classList.add('hidden');
        imgBefore.classList.remove('hidden');
        badge.textContent = 'BEFORE';
      }
    };

    window.likeExplorePost = function(id) {
      const el = document.getElementById(`like-count-${id}`);
      let count = parseInt(el.getAttribute('data-raw'), 10) + 1;
      el.setAttribute('data-raw', count);
      el.textContent = count >= 1000 ? (count/1000).toFixed(1) + 'k' : count;
    };

    window.commentExplorePost = function(id) {
      const el = document.getElementById(`comment-count-${id}`);
      let count = parseInt(el.getAttribute('data-raw'), 10) + 1;
      el.setAttribute('data-raw', count);
      el.textContent = count >= 1000 ? (count/1000).toFixed(1) + 'k' : count;
    };
  </script>
</body>

</html>
