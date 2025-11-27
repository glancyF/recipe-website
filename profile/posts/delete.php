<?php
/**
 * Delete recipe (authenticated + admin-aware API endpoint).
 *
 * This endpoint allows an authenticated user to delete their own recipe,
 * or an administrator to delete any recipe.
 * It validates authentication, permissions, and input, then executes the deletion.
 *
 * Behavior:
 * - Accepts only POST requests.
 * - Requires an active session with valid `user_id`.
 * - Admins can delete any recipe; regular users can delete only their own.
 * - Returns a JSON object indicating success or failure.
 *
 * Example success response:
 * ```json
 * { "status": "success", "message": "" }
 * ```
 *
 * Example error responses:
 * ```json
 * { "status": "error", "message": "Not authenticated" }
 * { "status": "error", "message": "Bad recipe id" }
 * { "status": "error", "message": "Recipe not found or not allowed" }
 * ```
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */

declare(strict_types=1);
global $conn;
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';
/**
 * Ensure the request method is POST.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Method not allowed']);
    exit;
}
/**
 * Authentication and user role.
 *
 * @var int    $userId  Current authenticated user ID.
 * @var bool   $isAdmin Whether the user has admin privileges.
 */
$userId  = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = (($_SESSION['status'] ?? '') === 'admin');
/**
 * Reject unauthenticated users.
 */
if (!$userId) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Not authenticated']);
    exit;
}
/**
 * Recipe ID to delete (must be a valid positive integer).
 *
 * @var int $recipeId
 */
$recipeId = (int)($_POST['id'] ?? 0);
if ($recipeId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Bad recipe id']);
    exit;
}

/**
 * Prepare the SQL query depending on user role.
 *
 * - Admins can delete any recipe.
 * - Regular users can only delete recipes they own.
 *
 * @var mysqli_stmt $stmt Prepared SQL statement.
 */
if ($isAdmin) {
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $recipeId);
} else {
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $recipeId, $userId);
}
/**
 * Execute deletion query and check the result.
 *
 * @var bool $ok True if a recipe was successfully deleted.
 */
$stmt->execute();
$ok = $stmt->affected_rows > 0;
$stmt->close();
/**
 * Send JSON response indicating result.
 */
echo json_encode([
    'status'  => $ok ? 'success' : 'error',
    'message' => $ok ? '' : 'Recipe not found or not allowed'
]);
