<?php
declare(strict_types=1);
global $conn;
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method not allowed']);
    exit;
}

$userId  = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = (($_SESSION['status'] ?? '') === 'admin');

if (!$userId) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Not authenticated']);
    exit;
}

$recipeId = (int)($_GET['id'] ?? 0);
if ($recipeId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Bad recipe id']);
    exit;
}


if ($isAdmin) {
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $recipeId);
} else {
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $recipeId, $userId);
}

$stmt->execute();
$ok = $stmt->affected_rows > 0;
$stmt->close();

echo json_encode([
    'status'  => $ok ? 'success' : 'error',
    'message' => $ok ? '' : 'Recipe not found or not allowed'
]);
