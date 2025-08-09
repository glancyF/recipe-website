<?php
global $conn;
require_once __DIR__ . '/../../includes/isAdmin.php';
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Forbidden']);
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Bad CSRF']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$status = $_POST['status'] ?? 'user';

if (!in_array($status, ['user','admin'], true)) {
    echo json_encode(['status'=>'error','message'=>'Invalid status']);
    exit;
}

if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
    echo json_encode(['status'=>'error','message'=>"You can't change your own status"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $userId);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['status'=>'success']);
} else {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB error']);
}
exit;
