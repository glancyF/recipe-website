<?php
/**
 * Fetch paginated list of recipes liked by the current user.
 *
 * This endpoint returns recipes that the authenticated user has liked.
 * Each recipe includes metadata, author info, like count, and the `liked` flag set to `true`.
 *
 * Behavior:
 * - Requires user authentication (403 Forbidden if not logged in).
 * - Supports pagination via `?page={number}` query parameter.
 * - Returns up to 4 recipes per page.
 * - Includes total liked recipe count, current page, and pagination limit.
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "recipes": [
 *     {
 *       "id": 5,
 *       "user_id": 3,
 *       "name": "Avocado Toast",
 *       "description": "Healthy and quick breakfast idea",
 *       "category": "breakfast",
 *       "image_path": "recipe_2025a.jpg",
 *       "created_at": "2025-10-15 10:00:00",
 *       "username": "chef_anna",
 *       "like_count": 12,
 *       "liked": true
 *     }
 *   ],
 *   "total": 23,
 *   "page": 1,
 *   "limit": 4
 * }
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
/**
 * Start session to identify authenticated user.
 */
session_start();
/**
 * Ensure user is logged in.
 */
if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    echo json_encode(array("error" => "Unauthorized"));
    exit;
}
/** @var int $user_id ID of the authenticated user */
$user_id = $_SESSION['user_id'];
/**
 * Pagination parameters.
 *
 * @var int $page   Current page number (default = 1)
 * @var int $limit  Recipes per page (fixed = 4)
 * @var int $offset SQL offset calculated from page and limit
 */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;
/**
 * SQL query to fetch recipes liked by the user.
 *
 * Joins:
 * - `recipe_likes` (user-liked relations)
 * - `recipes` (recipe details)
 * - `users` (recipe authors)
 *
 * Ordered by:
 * - Like count (descending)
 * - Recipe ID (descending, implicit)
 *
 * @var string $query
 */
$query = $query = "
    SELECT 
        r.id,
        r.user_id,
        r.name, 
        r.description, 
        r.category, 
        r.image_path, 
        r.created_at,
        u.username,
        (SELECT COUNT(*) FROM recipe_likes WHERE recipe_id = r.id) AS like_count
    FROM recipes r
    JOIN recipe_likes rl ON r.id = rl.recipe_id
    JOIN users u ON r.user_id = u.id
    WHERE rl.user_id = ?
    ORDER BY like_count DESC
    LIMIT ? OFFSET ?
";
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
/** @var mysqli_result $result */
$result = $stmt->get_result();
/**
 * Array of recipes liked by the user.
 *
 * @var array<int,array<string,mixed>> $recipes
 */
$recipes=$result->fetch_all(MYSQLI_ASSOC);
/**
 * Mark each recipe as liked.
 */
foreach($recipes as &$recipe){
    $recipe['liked'] = true;
}
unset($recipe);
/**
 * Count total liked recipes for pagination.
 *
 * @var mysqli_stmt $countStmt
 * @var array{total:int} $countResult
 * @var int $total
 */
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM recipe_likes WHERE user_id = ?");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$total = $countResult["total"];

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