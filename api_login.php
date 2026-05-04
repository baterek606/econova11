<?php
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'secure' => false,
    'httponly' => true
]);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $db = new SQLite3('econova.db');
        
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                
                header("Location: index.php?success=" . urlencode("Welcome back, " . $row['name'] . "!"));
                exit();
            } else {
                header("Location: login.php?error=" . urlencode("Wrong password. Please try again."));
                exit();
            }
        } else {
            header("Location: login.php?error=" . urlencode("Email not found. Create an account first."));
            exit();
        }
        $db->close();
    } catch (Exception $e) {
        header("Location: login.php?error=" . urlencode("Database error"));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
