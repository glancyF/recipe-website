<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';



function requireAuth(): ?array {
    global $conn;



    $token = $_COOKIE['auth_token'];

    $stmt = $conn->prepare("SELECT id, username, email, gender, status FROM users WHERE auth_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('Location: /login/auth.php');
        exit;
    }

    return $user;
}