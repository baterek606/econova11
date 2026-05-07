<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = null;
$joinedCampaigns = [];
$stats = ['trees' => 0, 'campaigns' => 0, 'plastic' => 0];
$badges = [
    'urban' => false,
    'zero' => false,
    'early' => false,
    'river' => false,
    'soil' => false
];

try {
    $db = new PDO('sqlite:econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure 'profile_image' column exists in 'users' table
    try {
        $db->exec("ALTER TABLE users ADD COLUMN profile_image TEXT");
    } catch (PDOException $e) {
        // Column probably exists
    }

    // Handle AJAX Profile Picture Upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
        header('Content-Type: application/json');
        
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.']);
            exit;
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Max size is 2MB.']);
            exit;
        }
        
        $targetDir = "uploads/profile_pictures/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $fileName = "user_" . $userId . "." . $ext;
        $targetFile = $targetDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?")->execute([$targetFile, $userId]);
            echo json_encode(['success' => true, 'image_path' => $targetFile, 'message' => 'Profile picture updated!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        }
        exit;
    }

    // Fetch user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch joined campaigns
    $stmt = $db->prepare("
        SELECT uc.joined_date, c.* 
        FROM user_campaigns uc 
        JOIN campaigns c ON uc.campaign_id = c.id 
        WHERE uc.user_id = ?
        ORDER BY uc.joined_date DESC
    ");
    $stmt->execute([$userId]);
    $joinedCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Stats (Realistic metrics: Each join = 5 trees, 0.5kg plastic)
    $stats['campaigns'] = count($joinedCampaigns);
    $stats['trees'] = $stats['campaigns'] * 5;
    $stats['plastic'] = $stats['campaigns'] * 0.5;

    foreach ($joinedCampaigns as $c) {
        // Logic for River Guardian: check if campaign title or location contains river
        if (strpos(strtolower($c['title'] ?? ''), 'river') !== false || strpos(strtolower($c['location'] ?? ''), 'river') !== false || strpos(strtolower($c['title'] ?? ''), 'nile') !== false) {
            $badges['river'] = true;
        }
    }

    // Calculate Badge Statuses
    if ($stats['campaigns'] >= 1) $badges['urban'] = true;
    if ($stats['trees'] >= 10) $badges['zero'] = true;
    if ($stats['campaigns'] >= 3) $badges['early'] = true;
    if ($stats['trees'] >= 50) $badges['soil'] = true;

} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Econova</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --bg-primary: #FCFAF6;
            --accent-green: #2e7d32;
            --text-main: #2A362C;
            --text-muted: #64746A;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .header { background: white; padding: 16px 40px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 24px; font-weight: 700; color: var(--accent-green); text-decoration: none; }
        .center-nav { display: flex; gap: 32px; }
        .nav-link { font-size: 14px; font-weight: 500; color: var(--text-muted); text-decoration: none; }
        .nav-actions { display: flex; align-items: center; gap: 16px; }
        .btn-create { background: #1a2e1f; color: white; padding: 8px 24px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; }

        .profile-wrapper { max-width: 1000px; margin: 60px auto; padding: 0 20px; }

        /* Profile Header Section */
        .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 40px; margin-bottom: 60px; }

        .profile-card-left { text-align: center; }
        .avatar-box { position: relative; width: 200px; height: 200px; margin: 0 auto 24px; }
        .profile-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 5px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        
        /* Floating Badges around Avatar */
        .floating-badge { 
            position: absolute; width: 40px; height: 40px; background: white; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; font-size: 18px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); border: 2px solid #fff;
        }
        .fb-1 { top: 10px; right: 0; background: #FEF3C7; }
        .fb-2 { bottom: 10px; right: 0; background: #D1FAE5; }

        .profile-name { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .profile-bio { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
        .tags { display: flex; justify-content: center; gap: 8px; }
        .tag { background: #E8F2EC; color: var(--accent-green); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }

        /* Pencil Icon Styles */
        .pencil-icon {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: var(--accent-green);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: transform 0.2s, background 0.2s;
            z-index: 5;
            border: 2px solid white;
        }
        .pencil-icon:hover {
            transform: scale(1.1);
            background: #1b5e20;
        }
        .pencil-icon svg { width: 18px; height: 18px; }

        #upload-msg {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 12px 24px;
            background: #2e7d32;
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: none;
            z-index: 1001;
            font-size: 14px;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-box { background: white; padding: 24px; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .stat-icon { font-size: 24px; margin-bottom: 8px; display: block; }
        .stat-number { font-size: 28px; font-weight: 700; display: block; color: var(--text-main); }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        /* Badges Section */
        .badges-container { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .section-header { font-size: 16px; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .badges-list { display: flex; gap: 32px; }
        .badge-circle { 
            width: 70px; height: 70px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; 
            background: #f3f4f6; color: #9ca3af; transition: all 0.3s; position: relative; cursor: help;
        }
        .badge-circle.unlocked { background: #E8F2EC; color: var(--accent-green); }
        .badge-circle span { font-size: 28px; }
        .badge-circle .tooltip { 
            position: absolute; bottom: -40px; background: #333; color: white; padding: 4px 12px; border-radius: 4px; 
            font-size: 11px; white-space: nowrap; opacity: 0; visibility: hidden; transition: 0.2s; z-index: 10;
        }
        .badge-circle:hover .tooltip { opacity: 1; visibility: visible; bottom: -45px; }
        .badge-label { font-size: 11px; font-weight: 600; margin-top: 8px; text-align: center; color: inherit; width: 80px; }

        /* Involvement Section */
        .involvement-section { margin-top: 80px; }
        .inv-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
        .inv-title h2 { font-size: 28px; font-weight: 700; margin-bottom: 6px; }
        .inv-title p { color: var(--text-muted); font-size: 14px; }
        .btn-view-all { border: 1px solid #ccc; padding: 10px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; background: white; cursor: pointer; text-decoration: none; color: inherit; }

        .campaign-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .camp-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: transform 0.2s; }
        .camp-card:hover { transform: translateY(-8px); }
        .camp-img { width: 100%; height: 200px; object-fit: cover; }
        .camp-body { padding: 24px; }
        .camp-date { font-size: 12px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 8px; }
        .camp-name { font-size: 18px; font-weight: 700; margin-bottom: 12px; color: var(--text-main); }
        .camp-desc { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; }
        .camp-footer { border-top: 1px solid #f3f4f6; padding-top: 16px; display: flex; gap: 20px; font-size: 13px; color: #999; }

        .locked { filter: grayscale(100%); opacity: 0.5; }

        .footer { background: #f8faf9; padding: 60px 40px 40px; border-top: 1px solid #eee; margin-top: 100px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 60px; max-width: 1000px; margin: 0 auto; }
        .footer-logo { font-size: 22px; font-weight: 700; margin-bottom: 20px; color: var(--accent-green); }
        .footer-links h4 { font-size: 12px; font-weight: 700; letter-spacing: 1px; margin-bottom: 24px; }
        .footer-links a { display: block; font-size: 14px; color: var(--text-muted); text-decoration: none; margin-bottom: 12px; }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">Econova</a>
        <nav class="center-nav">
            <a href="explore.php" class="nav-link">Explore</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
            <a href="map.php" class="nav-link">Map</a>
        </nav>
        <div class="nav-actions">
            <a href="profile.php" class="user-profile"><img src="https://i.pravatar.cc/100?u=<?php echo urlencode($userData['name'] ?? 'user'); ?>" alt="User" style="width:32px; height:32px; border-radius:50%;"></a>
            <a href="#" class="btn-create">Create</a>
        </div>
    </header>

    <main class="profile-wrapper">
        <div class="profile-grid">
            <div class="profile-card-left">
                <div class="avatar-box">
                    <?php 
                        $avatarUrl = !empty($userData['profile_image']) ? $userData['profile_image'] : 'https://i.pravatar.cc/150?u=' . urlencode($userData['name'] ?? 'user');
                    ?>
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" id="profile-display" class="profile-img" alt="User Profile">
                    
                    <!-- Pencil Icon Overlay -->
                    <label for="profile_pic_input" class="pencil-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                    </label>
                    <input type="file" id="profile_pic_input" style="display: none;" accept="image/*">

                    <?php if ($badges['urban']): ?>
                        <div class="floating-badge fb-1">🌳</div>
                    <?php endif; ?>
                    <?php if ($badges['zero']): ?>
                        <div class="floating-badge fb-2">♻️</div>
                    <?php endif; ?>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($userData['name'] ?? 'Eco Steward'); ?></h1>
                <p class="profile-bio">Environmental advocate dedicated to urban forestry and zero-waste living.</p>
                <div class="tags">
                    <span class="tag">Urban Forestry</span>
                    <span class="tag">Zero Waste</span>
                </div>
            </div>

            <div class="profile-main-right">
                <div class="stats-grid">
                    <div class="stat-box">
                        <span class="stat-icon">♻️</span>
                        <span class="stat-number"><?php echo $stats['plastic']; ?>kg</span>
                        <span class="stat-label">plastic removed</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-icon">📢</span>
                        <span class="stat-number"><?php echo $stats['campaigns']; ?></span>
                        <span class="stat-label">campaigns joined</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-icon">🌲</span>
                        <span class="stat-number"><?php echo $stats['trees']; ?></span>
                        <span class="stat-label">trees planted</span>
                    </div>
                </div>

                <div class="badges-container">
                    <div class="section-header">🏆 Environmental Badges</div>
                    <div class="badges-list">
                        <!-- Urban Forestry -->
                        <div class="badge-item">
                            <div class="badge-circle <?php echo $badges['urban'] ? 'unlocked' : 'locked'; ?>">
                                <span>🌳</span>
                                <div class="tooltip">Urban Forestry: Join 1 campaign</div>
                            </div>
                            <div class="badge-label">Urban Forestry</div>
                        </div>

                        <!-- Zero Waste -->
                        <div class="badge-item">
                            <div class="badge-circle <?php echo $badges['zero'] ? 'unlocked' : 'locked'; ?>">
                                <span>♻️</span>
                                <div class="tooltip">Zero Waste: Plant 10 trees</div>
                            </div>
                            <div class="badge-label">Zero Waste</div>
                        </div>

                        <!-- Early Steward -->
                        <div class="badge-item">
                            <div class="badge-circle <?php echo $badges['early'] ? 'unlocked' : 'locked'; ?>">
                                <span>⭐</span>
                                <div class="tooltip">Early Steward: Join 3 campaigns</div>
                            </div>
                            <div class="badge-label">Early Steward</div>
                        </div>

                        <!-- River Guardian -->
                        <div class="badge-item">
                            <div class="badge-circle <?php echo $badges['river'] ? 'unlocked' : 'locked'; ?>">
                                <span>🌊</span>
                                <div class="tooltip">River Guardian: Join a river campaign</div>
                            </div>
                            <div class="badge-label">River Guardian</div>
                        </div>

                        <!-- Soil Master -->
                        <div class="badge-item">
                            <div class="badge-circle <?php echo $badges['soil'] ? 'unlocked' : 'locked'; ?>">
                                <span>🌱</span>
                                <div class="tooltip">Soil Master: Plant 50 trees</div>
                            </div>
                            <div class="badge-label">Soil Master</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="involvement-section">
            <div class="inv-header">
                <div class="inv-title">
                    <h2>Personal Impact & Involvement</h2>
                    <p>Track your journey and current active campaigns</p>
                </div>
                <a href="#" class="btn-view-all">View All Posts</a>
            </div>

            <div class="campaign-grid">
                <?php if (empty($joinedCampaigns)): ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 60px; background: white; border-radius: 20px;">No involvement yet. Start your journey today!</p>
                <?php else: ?>
                    <?php foreach (array_slice($joinedCampaigns, 0, 3) as $camp): ?>
                        <div class="camp-card">
                            <img src="<?php echo htmlspecialchars($camp['image_url'] ?? 'forest.png'); ?>" class="camp-img" alt="Campaign">
                            <div class="camp-body">
                                <div class="camp-date"><?php echo date('M d, Y', strtotime($camp['joined_date'])); ?></div>
                                <h3 class="camp-name"><?php echo htmlspecialchars($camp['title'] ?? 'Campaign'); ?></h3>
                                <p class="camp-desc"><?php echo htmlspecialchars($camp['description'] ?? 'Join us in making a difference.'); ?></p>
                                <div class="camp-footer">
                                    <span>❤️ <?php echo rand(10, 50); ?></span>
                                    <span>💬 <?php echo rand(2, 10); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">Econova</div>
                <p>Nurturing stewardship for a sustainable future. Empowering communities to take ownership of their local environment through collective action.</p>
            </div>
            <div class="footer-links">
                <h4>ECOSYSTEM</h4>
                <a href="#">Community</a>
                <a href="#">Guidelines</a>
            </div>
            <div class="footer-links">
                <h4>SUPPORT</h4>
                <a href="#">Environmental Policy</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>

    <div id="upload-msg">Profile picture updated!</div>

    <script>
        document.getElementById('profile_pic_input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Preview instantly
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profile-display').src = event.target.result;
            };
            reader.readAsDataURL(file);

            // AJAX Upload
            const formData = new FormData();
            formData.append('profile_pic', file);

            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const msgBox = document.getElementById('upload-msg');
                msgBox.textContent = data.message;
                msgBox.style.background = data.success ? '#2e7d32' : '#d32f2f';
                msgBox.style.display = 'block';
                
                setTimeout(() => {
                    msgBox.style.display = 'none';
                }, 3000);

                if (data.success) {
                    // Refresh all images on page if necessary
                    document.getElementById('profile-display').src = data.image_path + '?v=' + new Date().getTime();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
