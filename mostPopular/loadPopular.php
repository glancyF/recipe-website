<?php
global $conn;
require_once __DIR__ . '/../db.php';
header('Content-type: application/json');

$page =isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset=($page-1)*$limit;
$query = "SELECT r.id, r.name, r.description, r.category, r.image_path, r.created_at,
       u.username,
       (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
FROM recipes r
JOIN users u ON r.user_id = u.id
ORDER BY like_count DESC
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);

session_start();
$user_id = $_SESSION['user_id'] ?? null;

foreach ($recipes as &$recipe) {
    $recipeId = $recipe['id'];
    $recipe['liked'] = false;

    if($user_id){
        $stmtCheck = $conn->prepare("SELECT 45 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $stmtCheck->bind_param('ii', $user_id, $recipeId);
        $stmtCheck->execute();
        $liked = $stmtCheck->get_result()->num_rows >0;
        $recipe['liked'] = $liked;
    }
}
unset($recipe);
$countResult = $conn->query("SELECT COUNT(*) as total FROM recipes")->fetch_assoc();
$total=$countResult['total'];
echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);

