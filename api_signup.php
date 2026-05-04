<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        try {
            $db = new SQLite3('econova.db');
            
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $check_stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $check_stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $check_result = $check_stmt->execute();

            if ($check_result->fetchArray(SQLITE3_ASSOC)) {
                header("Location: signup.php?error=" . urlencode("Email already exists. Use a different email or login."));
                exit();
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                $insert_stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $insert_stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $insert_stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
                
                if ($insert_stmt->execute()) {
                    $user_id = $db->lastInsertRowID();
                    
                    // Auto login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    header("Location: explore.php?success=" . urlencode("Account created successfully!"));
                    exit();
                } else {
                    header("Location: signup.php?error=" . urlencode("An error occurred during signup"));
                    exit();
                }
            }
            $db->close();
        } catch (Exception $e) {
            header("Location: signup.php?error=" . urlencode("Database error"));
            exit();
        }
    } else {
        header("Location: signup.php?error=" . urlencode("Please fill in all fields."));
        exit();
    }
} else {
    header("Location: signup.php");
    exit();
}
?>
