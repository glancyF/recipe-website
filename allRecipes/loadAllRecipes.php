<?php
/**
 * Fetch paginated list of recipes as JSON.
 *
 * This endpoint retrieves recipes from the database, including user info and like counts,
 * supports pagination via `page` query parameter, and includes whether the current user
 * liked each recipe (if authenticated via session).
 *
 * Behavior:
 * - Responds with application/json
 * - Returns recipe data (id, name, description, category, image, author, like count, etc.)
 * - Includes pagination info: total count, current page, limit
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "recipes": [...],
 *   "total": 57,
 *   "page": 2,
 *   "limit": 9
 * }
 *
 *
 * @author  Valentyn Deshel
 *
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
session_start();
/**
 * Pagination parameters.
 *
 * @var int $page   Current page number (default = 1)
 * @var int $limit  Recipes per page (fixed = 9)
 * @var int $offset SQL offset calculated from page and limit
 */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page -1) * $limit;
/**
 * SQL query for paginated recipes with:
 * - Recipe info
 * - Associated username
 * - Like count (via subquery)
 *
 * @var string $query
 */
$query = "
    SELECT r.id, r.user_id, r.name, r.description, r.category, r.image_path, r.created_at,
           u.username,
           (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
/** @var mysqli_result $result */
$result = $stmt->get_result();
/**
 * Array of recipes for current page.
 *
 * @var array<int,array<string,mixed>> $recipes
 */
$recipes = $result->fetch_all(MYSQLI_ASSOC);
/** @var int|null $user_id Current user ID from session (if logged in) */
$user_id = $_SESSION['user_id'] ?? null;
/**
 * Determine if the current user liked each recipe.
 */
foreach ($recipes as &$recipe) {
    /** @var int $recipeId */
    $recipeId = $recipe['id'];
    $recipe['liked'] = false;

    if($user_id){
        /** @var mysqli_stmt $likeCheck */
        $likeCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $likeCheck->bind_param("ii", $user_id, $recipeId);
        $likeCheck->execute();
        $recipe['liked'] = $likeCheck->get_result()->num_rows > 0;
    }
}
unset($recipe);
/**
 * Total recipe count for pagination.
 *
 * @var array{total:int} $count
 */
$count = $conn->query("SELECT COUNT(*) as total FROM recipes")->fetch_assoc();
/**
 * JSON response payload.
 */
echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $count['total'],
    'page' => $page,
    'limit' => $limit
]);