<?php
require_once 'db_connect.php';
require_once 'auth.php';

if (!isset($_GET['id'])) {
    header('Location: campaigns.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM campaigns WHERE id = ?');
$stmt->execute([$_GET['id']]);
$campaign = $stmt->fetch();

if (!$campaign) {
    die('Campaign not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Econova - <?php echo htmlspecialchars($campaign['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            margin-top: 40px;
            padding-bottom: 80px;
        }
        .details-content h1 {
            font-size: 36px;
            margin-bottom: 12px;
        }
        .stats-banner {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin: 32px 0;
            padding: 24px;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .map-wrapper {
            height: 400px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.05);
            position: sticky;
            top: 40px;
        }
        #map { height: 100%; width: 100%; }
        @media (max-width: 900px) {
            .details-container { grid-template-columns: 1fr; }
            .map-wrapper { height: 300px; position: static; }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <header class="header home-header">
            <a href="index.html" class="logo">Econova</a>
            <nav class="nav center-nav">
                <a href="explore.html" class="nav-link">Explore</a>
                <a href="campaigns.php" class="nav-link active">Campaigns</a>
                <a href="map.html" class="nav-link">Map</a>
            </nav>
            <div class="nav-actions">
                <a href="signup.html" class="btn btn-dark btn-sm">Create</a>
            </div>
        </header>

        <main class="details-container">
            <div class="details-content">
                <div class="badge">ACTIVE CAMPAIGN</div>
                <h1><?php echo htmlspecialchars($campaign['title']); ?></h1>
                <div class="card-location">
                    <span>📍</span> <?php echo htmlspecialchars($campaign['location']); ?>
                </div>
                
                <p class="hero-desc" style="margin-bottom: 24px;">
                    <?php echo nl2br(htmlspecialchars($campaign['description'])); ?>
                </p>

                <div class="progress-section">
                    <div class="progress-labels">
                        <strong class="text-green">Campaign Progress</strong>
                        <span><?php echo number_format($campaign['progress_percent']); ?>%</span>
                    </div>
                    <div class="progress-bar" style="background: #f0f0f0;">
                        <div class="progress-fill" style="width: <?php echo $campaign['progress_percent']; ?>%; background: var(--text-green);"></div>
                    </div>
                </div>

                <div class="stats-banner">
                    <div class="stat-item">
                        <span>TREES PLANTED</span>
                        <strong><?php echo number_format($campaign['trees_planted']); ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>VOLUNTEERS</span>
                        <strong><?php echo number_format($campaign['volunteers_count']); ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>EST. IMPACT</span>
                        <strong>4.2t CO2</strong>
                    </div>
                </div>

                <div style="display: flex; gap: 16px;">
                    <button class="btn btn-dark" style="flex: 2; padding: 16px;">Join this Campaign</button>
                    <button class="btn btn-outline" style="flex: 1; padding: 16px;">Share</button>
                </div>
            </div>

            <div class="map-wrapper">
                <div id="map"></div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">Econova</div>
                    <p>Nurturing stewardship for a sustainable future.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 Econova. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const lat = <?php echo (float)$campaign['latitude']; ?>;
        const lng = <?php echo (float)$campaign['longitude']; ?>;
        
        const map = L.map('map').setView([lat, lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<?php echo addslashes(htmlspecialchars($campaign['title'])); ?>')
            .openPopup();
    </script>
</body>
</html>
