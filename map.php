<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();
require_once 'functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$campaign = null;

try {
    $db = new PDO('sqlite:econova.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure missing columns exist
    $columnsToAdd = [
        'image_url' => 'TEXT',
        'plastic_removed_kg' => 'REAL DEFAULT 0',
        'goal' => 'TEXT DEFAULT "10,000 Saplings"',
        'stewards_count' => 'INTEGER DEFAULT 0',
        'trees_planted' => 'INTEGER DEFAULT 0',
        'progress_percent' => 'INTEGER DEFAULT 0',
        'latitude' => 'REAL',
        'longitude' => 'REAL'
    ];
    
    foreach ($columnsToAdd as $col => $type) {
        try {
            $db->exec("ALTER TABLE campaigns ADD COLUMN $col $type");
        } catch (PDOException $e) { /* Column likely exists */ }
    }

    // Seed sample images if they are missing
    $db->exec("UPDATE campaigns SET image_url = 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09' WHERE title LIKE '%Coastal%' AND image_url IS NULL");
    $db->exec("UPDATE campaigns SET image_url = 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e' WHERE title LIKE '%Nile%' AND image_url IS NULL");
    $db->exec("UPDATE campaigns SET image_url = 'https://images.unsplash.com/photo-1583212292454-1fe6228603b4' WHERE title LIKE '%Red Sea%' AND image_url IS NULL");

    // Fetch campaign data
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Quietly fail
}

if (!$campaign) {
    echo "<h1>Campaign not found</h1><p>Redirecting to campaigns...</p>";
    header("Refresh:3; url=campaigns.php");
    exit;
}

// Stats preparation
$title = $campaign['title'] ?? 'Campaign';
$location = $campaign['location'] ?? 'Location';
$goal = $campaign['goal'] ?? '10,000 Saplings';
$progress = $campaign['progress_percent'] ?? 0;
$trees = $campaign['trees_planted'] ?? 0;
$volunteers = $campaign['stewards_count'] ?? 0;
$lat = (float)($campaign['latitude'] ?? 30.0444);
$lng = (float)($campaign['longitude'] ?? 31.2357);

// Simple coordinate fallback
if ($lat == 30.0444 && $lng == 31.2357) {
    $loc = strtolower($location);
    if (strpos($loc, 'alexandria') !== false) { $lat = 31.2001; $lng = 29.9187; }
    elseif (strpos($loc, 'red sea') !== false) { $lat = 27.2579; $lng = 33.8128; }
}

// Handle Join (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'join') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to join', 'redirect' => 'login.php']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Create user_campaigns if missing
        $db->exec("CREATE TABLE IF NOT EXISTS user_campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            campaign_id INTEGER,
            joined_date DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Check for duplicate join
        $check = $db->prepare("SELECT id FROM user_campaigns WHERE user_id = ? AND campaign_id = ?");
        $check->execute([$userId, $id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already joined this campaign!']);
            exit;
        }

        // Check for plastic capacity (Max 2 kg per campaign)
        $cCheck = $db->prepare("SELECT plastic_removed_kg FROM campaigns WHERE id = ?");
        $cCheck->execute([$id]);
        $currentPlastic = $cCheck->fetchColumn();
        if ($currentPlastic >= 2.0) {
            echo json_encode(['success' => false, 'message' => 'Max capacity reached for this campaign!']);
            exit;
        }

        // 1. Update the campaigns table with realistic increments
        $db->prepare("UPDATE campaigns SET stewards_count = stewards_count + 1, trees_planted = trees_planted + 5, plastic_removed_kg = plastic_removed_kg + 0.5 WHERE id = ?")
           ->execute([$id]);
        
        // 2. Insert record into user_campaigns
        $db->prepare("INSERT INTO user_campaigns (user_id, campaign_id) VALUES (?, ?)")
           ->execute([$userId, $id]);

        // 3. Update Progress Percentage
        $c = $db->prepare("SELECT trees_planted, goal FROM campaigns WHERE id = ?");
        $c->execute([$id]);
        $cData = $c->fetch(PDO::FETCH_ASSOC);
        
        $currentTrees = (int)($cData['trees_planted'] ?? 0);
        $goalText = $cData['goal'] ?? '10000';
        $numericGoal = (int)filter_var($goalText, FILTER_SANITIZE_NUMBER_INT);
        if ($numericGoal <= 0) $numericGoal = 10000;
        
        $newProgress = round(($currentTrees / $numericGoal) * 100);
        if ($newProgress > 100) $newProgress = 100;
        
        $db->prepare("UPDATE campaigns SET progress_percent = ? WHERE id = ?")->execute([$newProgress, $id]);

        $new = $db->prepare("SELECT stewards_count, trees_planted, progress_percent, plastic_removed_kg FROM campaigns WHERE id = ?");
        $new->execute([$id]);
        $stats = $new->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'volunteers' => $stats['stewards_count'], 
            'trees' => $stats['trees_planted'],
            'progress' => $stats['progress_percent'],
            'plastic' => $stats['plastic_removed_kg']
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Econova - Map</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; height: 100vh; display: flex; flex-direction: column; }
        .header { background: white; padding: 16px 40px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: 700; color: #2e7d32; text-decoration: none; }
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { text-decoration: none; color: #666; font-weight: 500; font-size: 14px; }
        .nav-links a.active { color: #2e7d32; }

        .app-main { display: flex; flex: 1; overflow: hidden; position: relative; }
        .sidebar { 
            width: 380px; 
            background: white; 
            padding: 32px; 
            overflow-y: auto; 
            border-right: 1px solid #eee; 
            transition: all 0.3s ease;
            position: relative;
            z-index: 10;
        }
        .sidebar.collapsed { margin-left: -380px; opacity: 0; pointer-events: none; }
        
        .map-area { flex: 1; position: relative; transition: all 0.3s ease; }
        #map { width: 100%; height: 100%; }

        .toggle-btn { 
            position: absolute; top: 20px; left: 20px; z-index: 1001; 
            background: white; width: 40px; height: 40px; border-radius: 50%; 
            display: none; align-items: center; justify-content: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer; font-size: 20px;
        }
        .close-sidebar { 
            position: absolute; top: 20px; right: 20px; cursor: pointer; 
            font-size: 18px; color: #999; transition: color 0.2s; 
        }
        .close-sidebar:hover { color: #333; }

        .stat-card { background: #f9fafb; padding: 20px; border-radius: 12px; margin-bottom: 16px; }
        .stat-num { font-size: 24px; font-weight: 700; display: block; }
        .stat-label { font-size: 11px; color: #999; text-transform: uppercase; font-weight: 700; }

        .progress-bar { height: 8px; background: #eee; border-radius: 4px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: #2e7d32; }

        .btn-join { width: 100%; background: #2e7d32; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 20px; }
        .btn-join:disabled { opacity: 0.5; cursor: not-allowed; }

        .map-overlays { position: absolute; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px; }
        .pill { background: white; padding: 10px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">Econova</a>
        <nav class="nav-links">
            <a href="explore.php">Explore</a>
            <a href="campaigns.php">Campaigns</a>
            <a href="map.php?id=<?php echo $id; ?>" class="active">Map</a>
            <a href="profile.php">Profile</a>
        </nav>
        <div></div>
    </header>

    <div class="app-main">
        <aside class="sidebar" id="sidebar">
            <div class="close-sidebar" id="closeSidebar">✕</div>
            <h1 style="margin-bottom:8px; margin-right: 30px;"><?php echo htmlspecialchars($title); ?></h1>
            
            <?php if (!empty($campaign['image_url'])): ?>
                <div style="margin-bottom: 24px; border-radius: 12px; overflow: hidden; height: 150px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <img src="<?php echo htmlspecialchars($campaign['image_url']); ?>" alt="Campaign Image" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            <?php else: ?>
                <div style="margin-bottom: 24px; border-radius: 12px; height: 150px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #999; font-size: 13px;">No image yet</div>
            <?php endif; ?>

            <p style="color:#666; font-size:14px; margin-bottom:24px;">📍 <?php echo htmlspecialchars($location); ?></p>

            <div class="stat-card">
                <span class="stat-label">Progress to Goal</span>
                <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $progress; ?>%"></div></div>
                <span style="font-size:13px; font-weight:600; color:#2e7d32;"><?php echo $progress; ?>% of <?php echo htmlspecialchars($goal); ?></span>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="stat-card">
                    <span class="stat-num" id="vol-count"><?php echo $volunteers; ?></span>
                    <span class="stat-label">Volunteers</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num" id="tree-count"><?php echo $trees; ?></span>
                    <span class="stat-label">Trees</span>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <span class="stat-label">Capacity (Max 2kg)</span>
                <?php 
                    $plastic = (float)($campaign['plastic_removed_kg'] ?? 0); 
                    $capPercent = ($plastic / 2) * 100;
                ?>
                <div class="progress-bar"><div class="progress-fill" style="width:<?php echo $capPercent; ?>%; background: #3b82f6;"></div></div>
                <span style="font-size:12px; color:#666;"><?php echo 2.0 - $plastic; ?> kg removal capacity left</span>
            </div>

            <button class="btn-join" id="joinBtn" <?php echo ($plastic >= 2.0) ? 'disabled style="background:#999;"' : ''; ?>>
                <?php echo ($plastic >= 2.0) ? 'Max capacity reached' : 'Join this Campaign'; ?>
            </button>
            <p id="msg" style="text-align:center; font-size:13px; margin-top:12px; font-weight:600;"></p>
        </aside>

        <section class="map-area">
            <div id="map"></div>
            <div class="toggle-btn" id="openSidebar">☰</div>
            <div class="map-overlays">
                <div class="pill" id="toggleImpact" style="cursor:pointer;">Impact Sites</div>
                <div class="pill" id="toggleHubs" style="cursor:pointer;">Campaign Hubs</div>
                <button class="pill" id="findMeBtn" style="border:none; outline:none; font-family:inherit;">📍 Show My Location</button>
            </div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map)
            .bindPopup("<b><?php echo addslashes($title); ?></b><br><?php echo addslashes($location); ?>")
            .openPopup();

        // Impact Sites & Hubs Logic
        const impactLayer = L.layerGroup();
        const hubLayer = L.layerGroup();
        
        function generateNearbyMarkers(centerLat, centerLng, count, type) {
            const markers = [];
            const names = type === 'impact' 
                ? ["Mangrove Planting Zone", "Coastal Cleanup Area", "Seed Nursery", "Reef Restoration Site"]
                : ["Volunteer Meeting Point", "Equipment Center", "Registration Tent"];
            
            for (let i = 0; i < count; i++) {
                // Random offset roughly 0.3 to 1km (1 deg ~ 111km)
                const offsetLat = (Math.random() - 0.5) * 0.015;
                const offsetLng = (Math.random() - 0.5) * 0.015;
                const name = names[Math.floor(Math.random() * names.length)];
                markers.push({ lat: centerLat + offsetLat, lng: centerLng + offsetLng, name: name });
            }
            return markers;
        }

        const impactSites = generateNearbyMarkers(<?php echo $lat; ?>, <?php echo $lng; ?>, 3, 'impact');
        const hubs = generateNearbyMarkers(<?php echo $lat; ?>, <?php echo $lng; ?>, 2, 'hub');

        impactSites.forEach(s => {
            L.circleMarker([s.lat, s.lng], {
                radius: 8,
                fillColor: "orange",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).bindPopup(`<b>${s.name}</b><br>Impact Site`).addTo(impactLayer);
        });

        hubs.forEach(s => {
            L.circleMarker([s.lat, s.lng], {
                radius: 8,
                fillColor: "#8b5cf6", // Purple
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).bindPopup(`<b>${s.name}</b><br>Campaign Hub`).addTo(hubLayer);
        });

        document.getElementById('toggleImpact').addEventListener('click', function() {
            if (map.hasLayer(impactLayer)) {
                map.removeLayer(impactLayer);
                this.style.background = "white";
                this.style.color = "#666";
            } else {
                impactLayer.addTo(map);
                this.style.background = "orange";
                this.style.color = "white";
            }
        });

        document.getElementById('toggleHubs').addEventListener('click', function() {
            if (map.hasLayer(hubLayer)) {
                map.removeLayer(hubLayer);
                this.style.background = "white";
                this.style.color = "#666";
            } else {
                hubLayer.addTo(map);
                this.style.background = "#8b5cf6";
                this.style.color = "white";
            }
        });

        // Show My Location Logic
        let userMarker = null;
        let routeLine = null;

        document.getElementById('findMeBtn').addEventListener('click', function() {
            map.locate({setView: true, maxZoom: 15});
        });

        map.on('locationfound', function(e) {
            if (userMarker) map.removeLayer(userMarker);
            if (routeLine) map.removeLayer(routeLine);

            userMarker = L.circleMarker(e.latlng, {
                radius: 10,
                fillColor: "#3b82f6", // Blue
                color: "#fff",
                weight: 3,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map).bindPopup("You are here").openPopup();

            // Draw route line to campaign
            const campaignLatLng = [<?php echo $lat; ?>, <?php echo $lng; ?>];
            routeLine = L.polyline([e.latlng, campaignLatLng], {
                color: '#2e7d32',
                weight: 3,
                dashArray: '5, 10',
                opacity: 0.7
            }).addTo(map);

            // Calculate distance
            const distance = (map.distance(e.latlng, campaignLatLng) / 1000).toFixed(1);
            routeLine.bindPopup(`<b>Distance to Campaign:</b> ${distance} km`).openPopup();
            
            // Zoom to show both
            const bounds = L.latLngBounds([e.latlng, campaignLatLng]);
            map.fitBounds(bounds, {padding: [50, 50]});
        });

        const joinBtn = document.getElementById('joinBtn');
        const msg = document.getElementById('msg');
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar(hide) {
            if (hide) {
                sidebar.classList.add('collapsed');
                openBtn.style.display = 'flex';
            } else {
                sidebar.classList.remove('collapsed');
                openBtn.style.display = 'none';
            }
            // Wait for transition, then resize map
            setTimeout(() => {
                map.invalidateSize();
            }, 300);
        }

        closeBtn.addEventListener('click', () => toggleSidebar(true));
        openBtn.addEventListener('click', () => toggleSidebar(false));

        joinBtn.addEventListener('click', () => {
            joinBtn.disabled = true;
            const formData = new FormData();
            formData.append('action', 'join');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.success) {
                    document.getElementById('vol-count').textContent = data.volunteers;
                    document.getElementById('tree-count').textContent = data.trees;
                    
                    // Update main progress bar
                    const fills = document.querySelectorAll('.progress-fill');
                    if (fills[0]) fills[0].style.width = data.progress + '%';
                    
                    // Update plastic capacity bar if it exists
                    if (fills[1]) {
                        const plasticPercent = (data.plastic / 2) * 100;
                        fills[1].style.width = plasticPercent + '%';
                        const capText = fills[1].parentElement.nextElementSibling;
                        if (capText) capText.textContent = (2.0 - data.plastic).toFixed(1) + " kg removal capacity left";
                    }

                    msg.textContent = "Successfully joined!";
                    msg.style.color = "#2e7d32";

                    if (data.plastic >= 2.0) {
                        joinBtn.disabled = true;
                        joinBtn.textContent = "Max capacity reached";
                        joinBtn.style.background = "#999";
                    }
                } else {
                    msg.textContent = data.message;
                    msg.style.color = "red";
                    joinBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                joinBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
