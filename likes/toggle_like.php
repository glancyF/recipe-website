<?php
/**
 * Toggle recipe "like" status via AJAX.
 *
 * This endpoint allows authenticated users to like or unlike a recipe.
 * It toggles the record in the `recipe_likes` table and returns the
 * updated like count and the current like state in JSON format.
 *
 * Behavior:
 * - Requires POST method (405 returned otherwise)
 * - Requires authenticated session (Unauthorized returned if missing)
 * - Expects recipe ID via `?id={recipe_id}` in query string
 * - Toggles like state (insert/delete) in `recipe_likes` table
 * - Returns updated like count and current status (`liked`)
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "liked": true,
 *   "like_count": 12
 * }
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
session_start();
require_once __DIR__ . '/../db.php';

header('Content-type: application/json');
/**
 * Ensure request method is POST.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}
/**
 * Ensure user is authenticated via session.
 */
if(!isset($_SESSION['user_id'])){
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
/** @var int $userId ID of the currently authenticated user */
$userId = (int)$_SESSION['user_id'];
/** @var int $recipeId Target recipe ID extracted from query string */
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($recipeId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID']);
    exit;
}
/**
 * Check if user already liked the recipe.
 *
 * @var mysqli_stmt $stmt
 * @var mysqli_result $result
 */
$stmt = $conn->prepare("SELECT * FROM  recipe_likes WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param('ii',$userId,$recipeId);
$stmt->execute();
$result = $stmt->get_result();
/** @var bool $liked Whether recipe is liked after this request */
if($result->num_rows > 0) {
    // Already liked — remove like
    $del = $conn->prepare("DELETE FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $del->bind_param('ii',$userId,$recipeId);
    $del->execute();
    $liked = false;
}
else{
    // Not yet liked — add like
    $ins = $conn->prepare("INSERT INTO recipe_likes (user_id, recipe_id) VALUES (?,?) ");
    $ins->bind_param('ii',$userId,$recipeId);
    $ins->execute();
    $liked = true;
}
/**
 * Retrieve updated like count for this recipe.
 *
 * @var mysqli_stmt $countStmt
 * @var mysqli_result $countResult
 * @var array{total:int} $row
 * @var int $likeCount
 */
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
$countStmt->bind_param('i',$recipeId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$row = $countResult->fetch_assoc();
$likeCount = $row['total'];
/**
 * JSON response.
 */
echo json_encode([
    'status' => 'success',
    'liked' => $liked,
    'like_count' => $likeCount
]);
