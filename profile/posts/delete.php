<?php
global $conn;
require_once  __DIR__ . "/../../db.php";
session_start();
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}


$recipeId = $_GET['id'] ?? null;
if (!isset($_SESSION['user_id']) || !$recipeId) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid request']);
    exit;
}

$userId = $_SESSION['user_id'];

$query = "DELETE FROM recipes WHERE id = ? and user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $recipeId, $userId);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Recipe not found or not yours']);
}
$stmt->close();