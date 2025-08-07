<?php
global $conn;
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
session_start();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page -1) * $limit;

$query = "
    SELECT r.id, r.user_id, r.name, r.description, r.category, r.image_path, r.created_at,
           u.username,
           (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);

$user_id = $_SESSION['user_id'] ?? null;

foreach ($recipes as &$recipe) {
    $recipeId = $recipe['id'];
    $recipe['liked'] = false;

    if($user_id){
        $likeCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $likeCheck->bind_param("ii", $user_id, $recipeId);
        $likeCheck->execute();
        $recipe['liked'] = $likeCheck->get_result()->num_rows > 0;
    }
}
unset($recipe);

$count = $conn->query("SELECT COUNT(*) as total FROM recipes")->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $count['total'],
    'page' => $page,
    'limit' => $limit
]);