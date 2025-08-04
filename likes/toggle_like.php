<?php
global $conn;
session_start();
require_once __DIR__ . '/../db.php';

header('Content-type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}
if(!isset($_SESSION['user_id'])){
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$userId = (int)$_SESSION['user_id'];
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($recipeId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID']);
    exit;
}
$stmt = $conn->prepare("SELECT * FROM  recipe_likes WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param('ii',$userId,$recipeId);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    $del = $conn->prepare("DELETE FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $del->bind_param('ii',$userId,$recipeId);
    $del->execute();
    $liked = false;
}
else{
    $ins = $conn->prepare("INSERT INTO recipe_likes (user_id, recipe_id) VALUES (?,?) ");
    $ins->bind_param('ii',$userId,$recipeId);
    $ins->execute();
    $liked = true;
}

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
$countStmt->bind_param('i',$recipeId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$row = $countResult->fetch_assoc();
$likeCount = $row['total'];

echo json_encode([
    'status' => 'success',
    'liked' => $liked,
    'like_count' => $likeCount
]);
