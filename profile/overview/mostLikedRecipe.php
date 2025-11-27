<?php
/**
 * Fetch the most liked recipe of the currently logged-in user.
 *
 * This script retrieves the recipe created by the authenticated user
 * that has received the highest number of likes.
 * If the user has no recipes, `$topRecipe` will be `null`.
 *
 * Behavior:
 * - Requires active session and authentication.
 * - Calculates like counts via `LEFT JOIN` with `recipe_likes`.
 * - Returns one recipe record (the top liked one) or `null`.
 * - Adds `liked: true|false` to indicate whether the current user liked their own recipe.
 *
 * Example (pseudo-returned array):
 * ```php
 * [
 *   "id" => 42,
 *   "user_id" => 7,
 *   "name" => "Strawberry Smoothie",
 *   "description" => "Fresh and healthy summer drink",
 *   "category" => "drink",
 *   "image_path" => "smoothie_42.jpg",
 *   "created_at" => "2025-10-18 09:40:00",
 *   "like_count" => 18,
 *   "liked" => false
 * ]
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection (initialized elsewhere)
 */

global $conn;
/**
 * Ensure session is started.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Enforce user authentication.
 */
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access!');
}
/** @var int $userId ID of the currently logged-in user */
$userId = $_SESSION['user_id'];
/**
 * SQL query to fetch the most liked recipe owned by this user.
 *
 * Joins:
 * - `recipe_likes` table for counting likes.
 * - Uses `LEFT JOIN` to include recipes with zero likes.
 *
 * Orders by:
 * - Like count (descending)
 * - Recipe ID (descending implicit)
 *
 * @var string $query
 */
$query = "
SELECT r.*, COUNT(rl.id) AS like_count
FROM recipes r
LEFT JOIN recipe_likes rl ON r.id = rl.recipe_id
WHERE r.user_id = ?
GROUP BY r.id
ORDER BY like_count DESC
LIMIT 1
";
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
/** @var mysqli_result $result */
$result = $stmt->get_result();
/**
 * @var array<string,mixed>|null $topRecipe User's top recipe data or null if none exist.
 */
$topRecipe = $result->fetch_assoc();
/**
 * Check if the current user liked their own top recipe.
 */
if($topRecipe){
    /** @var mysqli_stmt $stmtCheck */
    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE recipe_id = ? AND user_id = ?");
    $stmtCheck->bind_param("ii", $topRecipe["id"], $userId);
    $stmtCheck->execute();
    /** @var bool $liked */
    $liked = $stmtCheck->get_result()->num_rows >0;
    $topRecipe["liked"] = $liked;
}
else{
    $topRecipe=null;
}
