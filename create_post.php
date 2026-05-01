<?php
session_start();

// Redirect to login if user is not authenticated
// Note: For testing purposes, if you haven't implemented login yet, you can comment these 4 lines out
// and hardcode a $user_id = 1; below.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Database connection parameters
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = "";     // Update with your DB password
$dbname = "econova_db";

$error = '';
$success = '';

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
$conn->query($sql);
$conn->select_db($dbname);

// Create table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS impact_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    before_image VARCHAR(255) NOT NULL,
    after_image VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    story TEXT NOT NULL,
    impact_stats TEXT,
    likes INT DEFAULT 0,
    comments INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($table_sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $location = $conn->real_escape_string($_POST['location']);
    $story = $conn->real_escape_string($_POST['story']);
    
    // Impact stats (optional)
    $plastic_removed = isset($_POST['plastic']) ? $conn->real_escape_string($_POST['plastic']) : '';
    $trees_planted = isset($_POST['trees']) ? $conn->real_escape_string($_POST['trees']) : '';
    $stats_json = json_encode(['plastic_lbs' => $plastic_removed, 'trees_planted' => $trees_planted]);

    // Ensure uploads directory exists
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $before_image = '';
    $after_image = '';
    $uploadOk = 1;

    // Handle Before Image
    if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] == 0) {
        $file_info = pathinfo($_FILES['before_image']['name']);
        $ext = strtolower($file_info['extension']);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $before_image = $upload_dir . uniqid('before_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['before_image']['tmp_name'], $before_image)) {
                $error = "Failed to upload before image.";
                $uploadOk = 0;
            }
        } else {
            $error = "Invalid file type for before image.";
            $uploadOk = 0;
        }
    } else {
        $error = "Before image is required.";
        $uploadOk = 0;
    }

    // Handle After Image
    if ($uploadOk == 1 && isset($_FILES['after_image']) && $_FILES['after_image']['error'] == 0) {
        $file_info = pathinfo($_FILES['after_image']['name']);
        $ext = strtolower($file_info['extension']);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $after_image = $upload_dir . uniqid('after_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['after_image']['tmp_name'], $after_image)) {
                $error = "Failed to upload after image.";
                $uploadOk = 0;
            }
        } else {
            $error = "Invalid file type for after image.";
            $uploadOk = 0;
        }
    } else if ($uploadOk == 1) {
        $error = "After image is required.";
        $uploadOk = 0;
    }

    // Insert to DB if uploads successful
    if ($uploadOk == 1 && empty($error)) {
        $insert_sql = "INSERT INTO impact_posts (user_id, title, category, before_image, after_image, location, story, impact_stats) 
                       VALUES ('$user_id', '$title', '$category', '$before_image', '$after_image', '$location', '$story', '$stats_json')";
        
        if ($conn->query($insert_sql) === TRUE) {
            $success = "Impact post created successfully! Thank you for your stewardship.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Impact Post - Econova</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="create_post.css">
</head>
<body>
  <div class="page-container">
    
    <!-- Top navigation bar -->
    <header class="header home-header">
      <a href="index.php" class="logo" style="color: var(--text-main);">Econova</a>
      
      <nav class="nav center-nav">
        <a href="explore.php" class="nav-link">Explore</a>
        <a href="#" class="nav-link">Campaigns</a>
        <a href="#" class="nav-link">Map</a>
      </nav>

      <div class="nav-actions">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" placeholder="Search stewardship...">
        </div>
        <div class="icons">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <span style="font-weight: 600; margin-left: 15px; margin-right: 15px; color: var(--text-main);">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="create_post.php" class="btn" style="background-color: var(--text-green); color: white; margin-right: 15px; text-decoration: none; font-size: 14px; padding: 8px 16px; border-radius: 20px;">+ New Impact</a>
        <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
      </div>
    </header>

    <main class="create-post-container">
      <div class="create-post-card">
        <div class="create-header">
          <h1>Share Your Impact</h1>
          <p>Inspire the community by sharing the transformation of your local environment.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <strong>Oops!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="create_post.php" method="POST" enctype="multipart/form-data" id="postForm">
          <div class="form-grid">
            
            <div class="form-group full-width">
              <label for="title">Post Title *</label>
              <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Weekend Mangrove Cleanup" required>
            </div>

            <div class="form-group">
              <label for="category">Ecosystem Category *</label>
              <select id="category" name="category" class="form-control" required>
                <option value="" disabled selected>Select category...</option>
                <option value="Forest">🌲 Forest</option>
                <option value="Beach">🏖️ Beach</option>
                <option value="Urban">🏙️ Urban</option>
                <option value="River">🌊 River</option>
              </select>
            </div>

            <div class="form-group">
              <label for="location">Location *</label>
              <input type="text" id="location" name="location" class="form-control" placeholder="e.g., Pismo Beach, CA" required>
            </div>

            <!-- Image Uploads -->
            <div class="form-group">
              <label>Before Image *</label>
              <div class="file-upload-wrapper">
                <input type="file" name="before_image" id="before_image" class="file-upload-input" accept="image/*" required onchange="updateFileName(this, 'before_name')">
                <svg class="upload-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <div class="upload-text" id="before_name">Click or drag to upload</div>
                <div class="upload-hint">PNG, JPG up to 5MB</div>
              </div>
            </div>

            <div class="form-group">
              <label>After Image *</label>
              <div class="file-upload-wrapper">
                <input type="file" name="after_image" id="after_image" class="file-upload-input" accept="image/*" required onchange="updateFileName(this, 'after_name')">
                <svg class="upload-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <div class="upload-text" id="after_name">Click or drag to upload</div>
                <div class="upload-hint">PNG, JPG up to 5MB</div>
              </div>
            </div>

            <div class="form-group full-width">
              <label for="story">Story / Description *</label>
              <textarea id="story" name="story" class="form-control" placeholder="Tell us about the project, the challenges you faced, and the community involved..." required></textarea>
            </div>

            <!-- Optional Impact Stats -->
            <div class="form-group full-width impact-stats-group">
              <div class="impact-stats-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                Impact Stats (Optional)
              </div>
              <div class="form-grid">
                <div class="form-group" style="margin-bottom: 0;">
                  <label for="plastic">Plastic Removed (lbs)</label>
                  <input type="number" id="plastic" name="plastic" class="form-control" placeholder="e.g., 50">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                  <label for="trees">Trees Planted</label>
                  <input type="number" id="trees" name="trees" class="form-control" placeholder="e.g., 200">
                </div>
              </div>
            </div>

            <div class="form-group full-width">
              <button type="submit" class="btn btn-dark btn-submit" id="submitBtn">Post Impact Story</button>
            </div>

          </div>
        </form>
      </div>
    </main>

    <footer class="footer" style="margin-top: 80px;">
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
          <a href="#">Help Center</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2024 Econova.</p>
      </div>
    </footer>
  </div>

  <script>
    // Update file name display on upload
    function updateFileName(input, textElementId) {
      const textElement = document.getElementById(textElementId);
      if (input.files && input.files.length > 0) {
        textElement.textContent = input.files[0].name;
        textElement.style.color = "var(--text-green)";
      } else {
        textElement.textContent = "Click or drag to upload";
        textElement.style.color = "var(--text-main)";
      }
    }

    // Simple loading state for button
    document.getElementById('postForm').addEventListener('submit', function() {
      const btn = document.getElementById('submitBtn');
      btn.innerHTML = 'Uploading... <svg width="18" height="18" style="animation: spin 1s linear infinite; margin-left: 8px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>';
      btn.classList.add('btn-loading');
    });
  </script>
  <style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
  <script src="script.js"></script>
</body>
</html>
