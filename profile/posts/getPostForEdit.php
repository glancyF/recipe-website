<?php
global $conn;
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/isAdmin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized access!';
    exit;
}

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipeId <= 0) {
    http_response_code(400);
    echo 'No recipe selected!';
    exit;
}

$userId  = (int)$_SESSION['user_id'];
$admin   = isAdmin();


if ($admin) {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $recipeId);
} else {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $recipeId, $userId);
}

$stmt->execute();
$res    = $stmt->get_result();
$recipe = $res->fetch_assoc();
$stmt->close();

if (!$recipe) {
    http_response_code(404);
    echo 'Recipe not found or access denied.';
    exit;
}


