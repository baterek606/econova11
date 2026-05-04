<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    // For this demonstration, we'll assume a session variable 'role'
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Helper to redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}
?>
