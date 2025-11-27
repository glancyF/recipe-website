<?php
/**
 * Fetch paginated list of most popular recipes.
 *
 * This endpoint returns recipes sorted by number of likes (descending).
 * It includes recipe details, author info, like counts, and whether the
 * currently authenticated user has liked each recipe.
 *
 * Behavior:
 * - Supports pagination via `?page={number}` query parameter.
 * - Returns up to 9 recipes per page.
 * - Includes total recipe count, current page, and per-page limit.
 * - If user session is active, includes `"liked": true|false` for each recipe.
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "recipes": [
 *     {
 *       "id": 5,
 *       "user_id": 2,
 *       "name": "Chocolate Cake",
 *       "description": "Rich dark chocolate cake",
 *       "category": "dessert",
 *       "image_path": "recipe_abc.jpg",
 *       "created_at": "2025-10-10 17:20:00",
 *       "username": "baker_jane",
 *       "like_count": 24,
 *       "liked": true
 *     }
 *   ],
 *   "total": 128,
 *   "page": 1,
 *   "limit": 9
 * }
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-type: application/json');
/**
 * Pagination parameters.
 *
 * @var int $page   Current page number (default = 1)
 * @var int $limit  Recipes per page (fixed = 9)
 * @var int $offset SQL offset calculated from current page
 */
$page =isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset=($page-1)*$limit;
/**
 * SQL query for popular recipes sorted by like count.
 *
 * Each recipe includes:
 * - Basic details (id, name, description, category, etc.)
 * - Author username (via JOIN)
 * - Calculated like count (via subquery)
 *
 * @var string $query
 */
$query = "SELECT r.id, r.user_id,r.name, r.description, r.category, r.image_path, r.created_at,
       u.username,
       (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
FROM recipes r
JOIN users u ON r.user_id = u.id
ORDER BY like_count DESC
LIMIT ? OFFSET ?";
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
/** @var mysqli_result $result */
$result = $stmt->get_result();
/**
 * Array of recipe records with aggregated data.
 *
 * @var array<int,array<string,mixed>> $recipes
 */
$recipes = $result->fetch_all(MYSQLI_ASSOC);

/** @var int|null $user_id Current user ID from session (if logged in) */
$user_id = $_SESSION['user_id'] ?? null;
/**
 * Determine "liked" status for each recipe for the current user.
 */
foreach ($recipes as &$recipe) {
    /** @var int $recipeId Recipe ID */
    $recipeId = $recipe['id'];
    $recipe['liked'] = false;

    if($user_id){
        /** @var mysqli_stmt $stmtCheck */
        $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $stmtCheck->bind_param('ii', $user_id, $recipeId);
        $stmtCheck->execute();
        /** @var bool $liked Whether the user has liked this recipe */
        $liked = $stmtCheck->get_result()->num_rows >0;
        $recipe['liked'] = $liked;
    }
}
unset($recipe);
/**
 * Retrieve total number of recipes for pagination info.
 *
 * @var array{total:int} $countResult
 * @var int $total
 */
$countResult = $conn->query("SELECT COUNT(*) as total FROM recipes")->fetch_assoc();
$total=$countResult['total'];
/**
 * JSON response payload.
 */
echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);

