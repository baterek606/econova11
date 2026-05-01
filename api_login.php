<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "error" => "Database connection failed."]);
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

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            echo json_encode([
                "success" => true, 
                "user" => [
                    "id" => $row['id'], 
                    "name" => $row['name'], 
                    "email" => $email
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid password."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No user found with that email address."]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}
?>
