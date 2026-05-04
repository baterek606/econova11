<?php
require_once 'db_connect.php';
require_once 'auth.php';

// Fetch all campaigns
$stmt = $pdo->query('SELECT * FROM campaigns ORDER BY created_at DESC');
$campaigns = $stmt->fetchAll();
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
    <style>
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
            margin-top: 40px;
        }
        .admin-form-section {
            background: var(--bg-badge);
            padding: 32px;
            border-radius: var(--radius-lg);
            margin-bottom: 48px;
            border: 1px solid var(--border-color);
        }
        .admin-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .admin-form .form-group.full {
            grid-column: span 2;
        }
        .campaign-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .card-img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }
        .card-body {
            padding: 24px;
        }
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }
        .card-location {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        .stat-item {
            text-align: center;
        }
        .stat-item span {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 4px;
        }
        .stat-item strong {
            font-size: 16px;
            color: var(--text-green);
        }
        .admin-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        .btn-delete {
            background-color: #ff4d4d;
            color: white;
        }
        .btn-edit {
            background-color: #f0f0f0;
            color: var(--text-main);
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

        <main style="padding: 40px 0;">
            <div class="section-header" style="margin-bottom: 40px;">
                <h1 class="hero-title">Ecological <span class="text-green">Impact</span> Campaigns</h1>
                <p class="hero-desc">Join our global network of stewards in restoring local ecosystems. Every tree planted and every hour volunteered brings us closer to a sustainable future.</p>
            </div>

            <?php if (isAdmin()): ?>
            <section class="admin-form-section">
                <h2 style="margin-bottom: 24px;">Add New Campaign (Admin)</h2>
                <form action="api/campaigns.php" method="POST" class="admin-form">
                    <div class="form-group">
                        <label>TITLE</label>
                        <input type="text" name="title" required placeholder="Campaign Title">
                    </div>
                    <div class="form-group">
                        <label>LOCATION</label>
                        <input type="text" name="location" required placeholder="City, Country">
                    </div>
                    <div class="form-group">
                        <label>GOAL (TREES)</label>
                        <input type="number" name="goal" required placeholder="50000">
                    </div>
                    <div class="form-group">
                        <label>IMAGE FILENAME</label>
                        <input type="text" name="image" placeholder="forest.png">
                    </div>
                    <div class="form-group">
                        <label>LATITUDE</label>
                        <input type="text" name="latitude" placeholder="-3.1190">
                    </div>
                    <div class="form-group">
                        <label>LONGITUDE</label>
                        <input type="text" name="longitude" placeholder="-60.0217">
                    </div>
                    <div class="form-group full">
                        <label>DESCRIPTION</label>
                        <textarea name="description" rows="3" style="width:100%; padding: 14px; border-radius: var(--radius-sm); border:none; background:white; font-family:inherit;" placeholder="Describe the campaign..."></textarea>
                    </div>
                    <div class="form-group full">
                        <button type="submit" class="btn btn-dark">Create Campaign</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>

            <div class="campaign-grid">
                <?php foreach ($campaigns as $campaign): ?>
                <div class="campaign-card">
                    <img src="<?php echo htmlspecialchars($campaign['image'] ?: 'forest.png'); ?>" alt="Campaign Image" class="card-img">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($campaign['title']); ?></h3>
                        <div class="card-location">
                            <span>📍</span> <?php echo htmlspecialchars($campaign['location']); ?>
                        </div>
                        
                        <div class="progress-section" style="margin: 16px 0;">
                            <div class="progress-labels">
                                <span><?php echo number_format($campaign['progress_percent']); ?>% Complete</span>
                                <span>Goal: <?php echo number_format($campaign['goal']); ?></span>
                            </div>
                            <div class="progress-bar" style="background: #f0f0f0; margin-bottom: 0;">
                                <div class="progress-fill" style="width: <?php echo $campaign['progress_percent']; ?>%; background: var(--text-green);"></div>
                            </div>
                        </div>

                        <div class="card-stats">
                            <div class="stat-item">
                                <span>Trees</span>
                                <strong><?php echo number_format($campaign['trees_planted']); ?></strong>
                            </div>
                            <div class="stat-item">
                                <span>Volunteers</span>
                                <strong><?php echo number_format($campaign['volunteers_count']); ?></strong>
                            </div>
                            <div class="stat-item">
                                <span>Days Left</span>
                                <strong>15</strong>
                            </div>
                        </div>

                        <div style="margin-top: 24px;">
                            <a href="campaign-details.php?id=<?php echo $campaign['id']; ?>" class="btn btn-outline w-full">View Details</a>
                        </div>

                        <?php if (isAdmin()): ?>
                        <div class="admin-actions">
                            <button class="btn btn-sm btn-edit" style="flex:1; padding: 8px;">Edit</button>
                            <button class="btn btn-sm btn-delete" style="flex:1; padding: 8px;" onclick="deleteCampaign(<?php echo $campaign['id']; ?>)">Delete</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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
                </div>
                <div>
                    <h4>SUPPORT</h4>
                    <a href="#">Contact</a>
                    <a href="#">FAQ</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 Econova. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        function deleteCampaign(id) {
            if (confirm('Are you sure you want to delete this campaign?')) {
                fetch(`api/campaigns.php?id=${id}`, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Error deleting campaign');
                });
            }
        }
    </script>
</body>
</html>
