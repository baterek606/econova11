<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin@econova.com') {
    http_response_code(403);
    die("403 Forbidden - Admins only.");
}

$db = new PDO('sqlite:econova.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables_stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
$tables = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Econova - Database Viewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .db-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        h1 { margin-bottom: 24px; color: var(--text-main); }
        h2 { margin-top: 40px; margin-bottom: 16px; color: var(--text-green); font-size: 20px; border-bottom: 2px solid #eee; padding-bottom: 8px;}
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8faf9;
            font-weight: 600;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
        }
        tr:hover {
            background-color: #fcfdfc;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <header class="header home-header">
          <a href="index.php" class="logo">Econova</a>
          <nav class="nav center-nav">
            <a href="explore.php" class="nav-link">Explore</a>
            <a href="campaigns.php" class="nav-link">Campaigns</a>
        <a href="leaderboard.php" class="nav-link">Leaderboard</a><!-- <a href="#" class="nav-link">Map</a> -->
          </nav>
          <div class="nav-actions">
            <div id="authWrapper" style="display: flex; align-items: center; gap: 16px;">
              <span style="font-weight: 600; color: var(--text-main);">Admin: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
              <a href="api_logout.php" class="btn btn-outline btn-sm" style="text-decoration:none;">Logout</a>
            </div>
          </div>
        </header>

        <main class="db-container">
            <h1>Database Viewer</h1>
            
            <?php foreach ($tables as $table): ?>
                <?php 
                    $tableName = $table['name'];
                    $rows_stmt = $db->query("SELECT * FROM \"$tableName\"");
                    $rows = $rows_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <h2>Table: <?php echo htmlspecialchars($tableName); ?></h2>
                
                <?php if (empty($rows)): ?>
                    <p style="color: #666; font-style: italic;">No records found in this table.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($rows[0]) as $column): ?>
                                        <th><?php echo htmlspecialchars($column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?php echo htmlspecialchars((string)$value); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
            <?php endforeach; ?>
        </main>
        
        <footer class="footer">
          <div class="footer-grid">
            <div class="footer-brand">
              <div class="logo">Econova</div>
            </div>
          </div>
          <div class="footer-bottom">
            <p>Econova © 2024. Admin Interface.</p>
          </div>
        </footer>
    </div>
</body>
</html>
