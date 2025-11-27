<?php
/**
 * Recipe details page controller.
 *
 * Loads a single recipe by its ID (from `$_GET['id']`) along with the author's
 * username, like count, and whether the current (logged-in) user liked it.
 * On failure, sets an HTTP status code, prepares `$code` and `$message`,
 * and includes the shared error template (`/error.php`).
 *
 * Behavior:
 * - Validates `id` query parameter (must be numeric).
 * - Queries recipe + author (`users.username`).
 * - Queries total like count.
 * - If session has `user_id`, computes `"liked"` flag for the viewer.
 * - Splits recipe ingredients into an array `$ingredients` (semicolon-separated).
 *
 * Error handling:
 * - 400 when `id` is missing/invalid → includes `error.php`.
 * - 404 when recipe is not found → includes `error.php`.
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
require_once __DIR__ . '/../db.php';
/**
 * Validate and read recipe ID from query string.
 *
 * @var int $recipeId
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    /** @var int $code */
    $code = 400;
    /** @var string $message */
    $message = 'No valid recipe selected!';
    include __DIR__ . '/../error.php';
    exit;
}

$recipeId = (int)$_GET['id'];
/**
 * Fetch the recipe row and author username.
 *
 * @var mysqli_stmt $stmt
 * @var mysqli_result $result
 * @var array<string,mixed>|null $recipe
 */
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
if(!$stmt){
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $recipeId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();
/* Handle not found */
if(!$recipe){
    http_response_code(404);
    /** @var int $code */
    $code = 404;
    /** @var string $message */
    $message = 'Recipe not found!';
    include __DIR__ . '/../error.php';
    exit;
}
/**
 * Compute total like count for the recipe.
 *
 * @var mysqli_stmt $stmtLikes
 * @var array{total:int} $resLikes
 */
$stmtLikes = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
$stmtLikes->bind_param("i", $recipeId);
$stmtLikes->execute();
$resLikes = $stmtLikes->get_result()->fetch_assoc();
$recipe['like_count'] = (int)$resLikes['total'];
/**
 * Determine whether the current user liked the recipe (if session user available).
 *
 * @var bool $liked
 */
$recipe['liked'] = false;
if (isset($_SESSION['user_id'])) {
    /** @var mysqli_stmt $stmtCheck */
    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $stmtCheck->bind_param("ii", $_SESSION['user_id'], $recipeId);
    $stmtCheck->execute();
    $liked = $stmtCheck->get_result()->num_rows > 0;
    $recipe['liked'] = $liked;
}
/**
 * Split ingredients string into an array for template usage.
 *
 * @var array<int,string> $ingredients
 */
$ingredients = array_filter(array_map('trim', explode(';', $recipe['ingredients'])));