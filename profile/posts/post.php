<?php
global $conn;
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$user_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;

$query = "SELECT id, name, description, category, image_path, created_at FROM recipes WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);
foreach ($recipes as &$recipe) {
    $recipeId = $recipe['id'];


    $stmtLikes = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
    $stmtLikes->bind_param("i", $recipeId);
    $stmtLikes->execute();
    $resLikes = $stmtLikes->get_result()->fetch_assoc();
    $recipe['like_count'] = (int)$resLikes['total'];


    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $stmtCheck->bind_param("ii", $user_id, $recipeId);
    $stmtCheck->execute();
    $liked = $stmtCheck->get_result()->num_rows > 0;
    $recipe['liked'] = $liked;
}
unset($recipe);


$countQuery = "SELECT COUNT(*) As total FROM recipes WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$total = isset($countRow['total']) ? (int)$countRow['total'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'recipes' => $recipes,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    exit;
}