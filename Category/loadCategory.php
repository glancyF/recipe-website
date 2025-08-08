<?php
global $conn;
require_once __DIR__ . '/../db.php';
header('Content-type: application/json');
session_start();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page-1)*$limit;
$category = $_GET['category'] ?? 'all';

$categoryCondition = $category !== 'all' ? " WHERE r.category = ?" : "";
$params = [];
$types = "";
$query = "
    SELECT r.id, r.user_id, r.name, r.description, r.category, r.image_path, r.created_at,
           u.username,
           (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    $categoryCondition
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if($category !== 'all'){
    $stmt->bind_param("sii",$category,$limit,$offset);
}
else{
    $stmt->bind_param("ii",$limit,$offset);
}
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);
$user_id = $_SESSION['user_id'] ?? null;

foreach ($recipes as &$recipe) {
    $recipeId=$recipe['id'];
    $recipe['liked'] = false;
    if($user_id){
        $likeCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $likeCheck->bind_param("ii", $user_id, $recipeId);
        $likeCheck->execute();
        $recipe['liked'] = $likeCheck->get_result()->num_rows>0;

    }
}
unset($recipe);

$countQuery = $category !== 'all'
    ? $conn->prepare("SELECT COUNT(*) AS total FROM recipes WHERE category = ?")
    : $conn->prepare("SELECT COUNT(*) AS total FROM recipes");
if($category !== 'all'){
    $countQuery->bind_param("s", $category);
}
$countQuery->execute();
$total = $countQuery->get_result()->fetch_assoc()['total'];

echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);