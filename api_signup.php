<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        $conn = new mysqli("localhost", "root", "");
        
        if ($conn->connect_error) {
            echo json_encode(["success" => false, "error" => "Connection failed."]);
            exit();
        }
        
        $conn->query("CREATE DATABASE IF NOT EXISTS econova_db");
        $conn->select_db("econova_db");
        
        $table_sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($table_sql);

        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo json_encode(["success" => false, "error" => "Email is already registered. Please log in."]);
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "An error occurred while creating your account."]);
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
        $conn->close();
    } else {
        echo json_encode(["success" => false, "error" => "Please fill in all fields."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}
?>
