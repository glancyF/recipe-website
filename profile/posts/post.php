<?php
/**
 * Fetch paginated list of recipes created by the currently logged-in user.
 *
 * This endpoint retrieves recipes authored by the authenticated user,
 * including like counts and whether the user has liked each of them.
 *
 * Behavior:
 * - Requires user authentication (403 if unauthorized).
 * - Accepts pagination via `?page={number}` query parameter.
 * - Returns up to 4 recipes per page.
 * - Each recipe includes its metadata, like count, and a `liked` boolean.
 *
 * Example JSON response:
 * ```json
 * {
 *   "status": "success",
 *   "recipes": [
 *     {
 *       "id": 5,
 *       "name": "Chocolate Cake",
 *       "description": "Moist and rich homemade cake",
 *       "category": "dessert",
 *       "image_path": "recipe_5.jpg",
 *       "created_at": "2025-10-18 12:30:00",
 *       "like_count": 8,
 *       "liked": true
 *     }
 *   ],
 *   "total": 12,
 *   "page": 1,
 *   "limit": 4
 * }
 * ```
 *
 * Error responses:
 * ```json
 * { "status": "error", "message": "Unauthorized" }
 * ```
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
/**
 * Ensure the session is active.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Enforce user authentication.
 */
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
/** @var int $user_id ID of the authenticated user */
$user_id = $_SESSION['user_id'];
/**
 * Pagination parameters.
 *
 * @var int $page   Current page number (default = 1)
 * @var int $limit  Recipes per page (fixed = 4)
 * @var int $offset SQL offset
 */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;
/**
 * SQL query to fetch user-owned recipes.
 *
 * @var mysqli_stmt $stmt
 * @var mysqli_result $result
 * @var array<int,array<string,mixed>> $recipes
 */
$query = "SELECT id, name, description, category, image_path, created_at FROM recipes WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);
/**
 * Append like count and 'liked' flag to each recipe.
 */
foreach ($recipes as &$recipe) {
    $recipeId = $recipe['id'];
    // --- Like count ---

    $stmtLikes = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
    $stmtLikes->bind_param("i", $recipeId);
    $stmtLikes->execute();
    $resLikes = $stmtLikes->get_result()->fetch_assoc();
    $recipe['like_count'] = (int)$resLikes['total'];

    // --- Liked by current user? ---
    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $stmtCheck->bind_param("ii", $user_id, $recipeId);
    $stmtCheck->execute();
    $liked = $stmtCheck->get_result()->num_rows > 0;
    $recipe['liked'] = $liked;
}
unset($recipe);

/**
 * Count total number of user's recipes for pagination metadata.
 *
 * @var mysqli_stmt $countStmt
 * @var array{total:int}|null $countRow
 * @var int $total
 */
$countQuery = "SELECT COUNT(*) As total FROM recipes WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$total = isset($countRow['total']) ? (int)$countRow['total'] : 0;
/**
 * Return paginated JSON response when page parameter is set.
 */
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