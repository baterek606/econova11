<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
require_once 'functions.php';

$leaderboard = [];
try {
    $db = new PDO('sqlite:econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Dynamic real-time calculation as requested
    $sql = "SELECT 
                u.id, 
                u.name, 
                u.profile_image,
                (SELECT COUNT(DISTINCT campaign_id) FROM user_campaigns WHERE user_id = u.id) AS campaigns_joined,
                IFNULL((SELECT SUM(trees_planted) FROM user_stats WHERE user_id = u.id), 0) AS trees_planted,
                IFNULL((SELECT SUM(plastic_removed_kg) FROM user_stats WHERE user_id = u.id), 0) AS plastic_removed_kg,
                (
                    (SELECT COUNT(DISTINCT campaign_id) FROM user_campaigns WHERE user_id = u.id) * 100 + 
                    IFNULL((SELECT SUM(trees_planted) FROM user_stats WHERE user_id = u.id), 0) * 50 + 
                    IFNULL((SELECT SUM(plastic_removed_kg) FROM user_stats WHERE user_id = u.id), 0) * 10
                ) AS score
            FROM users u
            ORDER BY score DESC, u.id ASC
            LIMIT 10";
            
    $stmt = $db->query($sql);
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error quietly
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Econova</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <style>
        body {   background-color: var(--bg-primary);}
        .leaderboard-hero {
            background-color: #e8f2ec;
            padding: 60px 20px;
            text-align: center;
        }
        .leaderboard-hero h1 {
            font-size: 36px;
            color: #2d6a4f;
            margin-bottom: 16px;
        }
        .leaderboard-hero p {
            color: #4a5568;
            max-width: 600px;
            margin: 0 auto;
            font-size: 16px;
            line-height: 1.5;
        }
        .table-wrapper {
            max-width: 1000px;
            margin: -40px auto 60px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        .leaderboard-table th {
            text-align: left;
            padding: 16px;
            border-bottom: 2px solid #eee;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .leaderboard-table td {
            padding: 20px 16px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .leaderboard-table tr:last-child td {
            border-bottom: none;
        }
        .rank-col {
            font-size: 20px;
            font-weight: 700;
            color: #2d6a4f;
            width: 60px;
            text-align: center;
        }
        .user-col {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
        }
        .stats-col div {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .stats-col strong {
            color: #374151;
        }
        .score-col {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
            text-align: right;
        }
        .top-3-rank {
            background: #fef3c7;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #b45309;
        }
        .rank-1 { background: #fef08a; color: #854d0e; }
        .rank-2 { background: #e5e7eb; color: #374151; }
        .rank-3 { background: #fed7aa; color: #9a3412; }
    </style>
</head>
<body>
  <div class="page-container">
    <header class="header home-header">
      <a href="index.php" class="logo">Econova</a>

      <nav class="nav center-nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="campaigns.php" class="nav-link">Campaigns</a>
        <a href="leaderboard.php" class="nav-link active">Leaderboard</a>
      </nav>

      <div class="nav-actions">
         
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="icons">
         <a href="profile.php"> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg></a>
        </div>
        <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
          <span <?php if(getUserScore($_SESSION['user_id']) >= 500): ?>class="score-badge tooltip-enabled" onclick="openRewardsModal()"<?php else: ?>class="score-badge"<?php endif; ?> style="font-weight: 600; color: #2e7d32; background: #e8f2ec; padding: 4px 12px; border-radius: 20px; font-size: 14px; position: relative; cursor: <?php echo (getUserScore($_SESSION['user_id']) >= 500) ? 'pointer' : 'default'; ?>;">🌱 <?php echo number_format(getUserScore($_SESSION['user_id'])); ?> pts</span>
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
      <section class="leaderboard-hero">
        <h1>Global Stewardship Leaderboard</h1>
        <p>Recognizing the top contributors to our planet. Earn points by joining campaigns, planting trees, cleaning up plastic, and inspiring others.</p>
      </section>

      <section class="table-wrapper">
        <table class="leaderboard-table">
          <thead>
            <tr>
              <th style="text-align:center;">Rank</th>
              <th>Steward</th>
              <th>Impact Highlights</th>
              <th style="text-align:right;">Total Score</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leaderboard as $index => $user): 
                $rank = $index + 1;
                $rankClass = $rank <= 3 ? "top-3-rank rank-$rank" : "";
                $avatar = !empty($user['profile_image']) ? $user['profile_image'] : 'https://i.pravatar.cc/150?u=' . urlencode($user['name']);
                
                $trees = $user['trees_planted'];
                $plastic = $user['plastic_removed_kg'];
            ?>
            <tr>
              <td class="rank-col">
                <span class="<?php echo $rankClass; ?>"><?php echo $rank; ?></span>
              </td>
              <td>
                <div class="user-col">
                  <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="user-img">
                  <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
              </td>
              <td class="stats-col">
                <div><strong><?php echo $trees; ?></strong> Trees Planted</div>
                <div><strong><?php echo $user['campaigns_joined']; ?></strong> Campaigns Joined</div>
                <div><strong><?php echo $plastic; ?> kg</strong> Plastic Removed</div>
              </td>
              <td class="score-col">
                🌱 <?php echo number_format($user['score']); ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo">Econova</div>
          <p>Nurturing stewardship for a sustainable future. Empowering communities to take ownership of their local environment through collective action.</p>
        </div>
        <div>
          <h4>RESOURCES</h4>
          <a href="#">Community</a>
          <a href="#">Guidelines</a>
          <a href="leaderboard.php">Leaderboard</a>
        </div>
        <div>
          <h4>SUPPORT</h4>
          <a href="#">Contact</a>
          <a href="#">FAQ</a>
        </div>
      </div>
    </footer>
  </div>
</body>
</html>
