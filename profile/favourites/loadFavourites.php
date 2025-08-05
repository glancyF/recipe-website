<?php
global $conn;
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    echo json_encode(array("error" => "Unauthorized"));
    exit;
}
$user_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;
$query = "SELECT r.id, r.name, r.description, r.category, r.image_path, r.created_at,
             (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
          FROM recipes r
          JOIN recipe_likes rl ON r.id = rl.recipe_id
          WHERE rl.user_id = ?
          ORDER BY like_count DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes=$result->fetch_all(MYSQLI_ASSOC);

foreach($recipes as &$recipe){
    $recipe['liked'] = true;
}
unset($recipe);

$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM recipe_likes WHERE user_id = ?");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$total = $countResult["total"];


echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);