<?php

require_once __DIR__ .'/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isAdmin(): bool {
    if (!isset($_SESSION['user_id'])) return false;
    global $conn;
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $status = $stmt->get_result()->fetch_column();
    return $status === 'admin';
}