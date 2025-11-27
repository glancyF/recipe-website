<?php
/**
 * Fetch the most popular recipe (highest like count).
 *
 * This endpoint returns a single recipe that has the most likes.
 * If multiple recipes share the same count, the most recently created one is chosen.
 * The response includes recipe details, author info, total like count, and
 * whether the currently logged-in user liked it.
 *
 * Behavior:
 * - If no recipes exist → returns JSON `{ "status": "empty" }`
 * - If recipes exist → returns JSON `{ "status": "success", "recipe": {...} }`
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "recipe": {
 *     "id": 15,
 *     "user_id": 3,
 *     "name": "Pancakes",
 *     "description": "Fluffy morning pancakes",
 *     "category": "breakfast",
 *     "image_path": "recipe_1234.jpg",
 *     "created_at": "2025-10-18 09:32:00",
 *     "username": "chef_anna",
 *     "like_count": 42,
 *     "liked": true
 *   }
 * }
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
header('Content-Type: application/json');
/**
 * Ensure session is started.
 */
if(PHP_SESSION_NONE===session_status()){
    session_start();
}

require_once __DIR__ . '/../db.php';
/**
 * SQL query to fetch the most liked recipe.
 *
 * Joins recipes with users and aggregates like counts.
 * Orders by like count (descending), then by recipe ID (descending).
 *
 * @var string $sql
 */
$sql = "
  SELECT 
    r.id, r.user_id, r.name, r.description, r.category, r.image_path, r.created_at,
    u.username,
    COALESCE(rl.cnt, 0) AS like_count
  FROM recipes r
  JOIN users u ON u.id = r.user_id
  LEFT JOIN (
    SELECT recipe_id, COUNT(*) AS cnt 
    FROM recipe_likes 
    GROUP BY recipe_id
  ) rl ON rl.recipe_id = r.id
  ORDER BY COALESCE(rl.cnt, 0) DESC, r.id DESC
  LIMIT 1
";
/** @var mysqli_result|false $res */
$res = $conn->query($sql);
/**
 * @var array<string,mixed>|null $recipe The most popular recipe data, or null if none exist.
 */
$recipe = $res ? $res->fetch_assoc() : null;
/* -------------------- Handle empty result -------------------- */
if(!$recipe){
    echo json_encode(['status'=>'empty']);
    exit;
}
/** @var int|null $userId Current logged-in user ID, if any. */
$userId=$_SESSION['user_id'] ?? null;

$recipe['liked'] = false;
if($userId){
    /** @var mysqli_stmt $stmt */
    $stmt = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ? LIMIT 1");
    $stmt->bind_param('ii', $userId, $recipe['id']);
    $stmt->execute();
    $recipe['liked'] = $stmt->get_result()->num_rows > 0;
}
/**
 * JSON response.
 */
echo json_encode([
    'status' => 'success',
    'recipe' => $recipe
]);
