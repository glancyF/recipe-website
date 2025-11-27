<?php
/**
 * Fetch paginated and optionally filtered list of recipes as JSON.
 *
 * This endpoint returns recipes with pagination and optional category filtering.
 * It includes user information, like counts, and whether the current user liked
 * each recipe (if authenticated via session).
 *
 * Behavior:
 * - Supports category filter via ?category={category|all}
 * - Supports pagination via ?page={number}
 * - Returns JSON with status, recipes, total count, page, and limit
 *
 * Example:
 *   /api/recipes_by_category.php?page=2&category=dessert
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "recipes": [...],
 *   "total": 34,
 *   "page": 2,
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
header('Content-type: application/json');
session_start();
/**
 * Pagination and filter parameters.
 *
 * @var int    $page     Current page number (default = 1)
 * @var int    $limit    Number of recipes per page (fixed = 9)
 * @var int    $offset   SQL offset value
 * @var string $category Selected category or "all"
 */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page-1)*$limit;
$category = $_GET['category'] ?? 'breakfast';
/**
 * Optional category filter condition.
 *
 * @var string $categoryCondition SQL WHERE clause for category filtering
 */
$categoryCondition = " WHERE r.category = ?";
/** @var array<int,mixed> $params Bound parameters for prepared statement */
$params = [];
/** @var string $types Type string for mysqli bind_param */
$types = "";
/**
 * SQL query for recipes with user info and like counts.
 *
 * Includes subquery for like count and optional category filtering.
 *
 * @var string $query
 */
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
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare($query);
$stmt->bind_param("sii",$category,$limit,$offset);
$stmt->execute();
/** @var mysqli_result $result */
$result = $stmt->get_result();
/**
 * Array of recipes fetched for the current page.
 *
 * Each element contains:
 * - id (int)
 * - user_id (int)
 * - name (string)
 * - description (string)
 * - category (string)
 * - image_path (string)
 * - created_at (string)
 * - username (string)
 * - like_count (int)
 *
 * @var array<int,array<string,mixed>> $recipes
 */
$recipes = $result->fetch_all(MYSQLI_ASSOC);
/** @var int|null $user_id Current logged-in user ID (if any) */
$user_id = $_SESSION['user_id'] ?? null;
/**
 * For authenticated users, determine if they liked each recipe.
 */
foreach ($recipes as &$recipe) {
    /** @var int $recipeId */
    $recipeId=$recipe['id'];
    $recipe['liked'] = false;
    if($user_id){
        /** @var mysqli_stmt $likeCheck */
        $likeCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
        $likeCheck->bind_param("ii", $user_id, $recipeId);
        $likeCheck->execute();
        $recipe['liked'] = $likeCheck->get_result()->num_rows>0;

    }
}
unset($recipe);
/**
 * Count total number of recipes (with or without category filter).
 *
 * @var mysqli_stmt $countQuery
 */
$countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM recipes WHERE category = ?");
$countQuery->bind_param("s", $category);
$countQuery->execute();
/**
 * @var int $total Total number of matching recipes
 */
$total = $countQuery->get_result()->fetch_assoc()['total'];

/**
 * JSON response with paginated recipe data.
 */
echo json_encode([
    'status' => 'success',
    'recipes' => $recipes,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);