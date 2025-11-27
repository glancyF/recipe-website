<?php
/**
 * Fetch a single recipe for editing (admin or owner only).
 *
 * This script retrieves a recipe record from the database for the edit form.
 * Access is restricted to:
 * - The recipe owner (authenticated user), or
 * - Administrators (`isAdmin()` returns true).
 *
 * Behavior:
 * - Requires user authentication via session.
 * - Accepts recipe ID via GET parameter `id`.
 * - If admin, can access any recipe.
 * - If regular user, can only access their own recipe.
 * - Returns the recipe as an associative array (to be used by the edit page).
 *
 * Security:
 * - Prevents unauthorized access (401 if not logged in).
 * - Prevents access to others' recipes (404 if not found or forbidden).
 *
 * Example usage:
 * ```
 * GET /recipes/edit.php?id=12
 * ```
 *
 * Example error responses:
 * ```
 * 401 Unauthorized access!
 * 400 No recipe selected!
 * 404 Recipe not found or access denied.
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
require_once __DIR__ . '/../../includes/isAdmin.php';
/**
 * Start a session if not already started.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Ensure the user is logged in.
 */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized access!';
    exit;
}
/**
 * Retrieve recipe ID from GET query parameters.
 *
 * @var int $recipeId ID of the recipe to edit.
 */
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipeId <= 0) {
    http_response_code(400);
    echo 'No recipe selected!';
    exit;
}
/**
 * Current user ID and role check.
 *
 * @var int  $userId Current authenticated user ID.
 * @var bool $admin  Whether the current user is an admin.
 */
$userId  = (int)$_SESSION['user_id'];
$admin   = isAdmin();
/**
 * Prepare the query.
 *
 * Admin users can fetch any recipe; regular users only their own.
 *
 * @var mysqli_stmt $stmt
 */

if ($admin) {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $recipeId);
} else {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $recipeId, $userId);
}
/**
 * Execute the query and fetch the recipe data.
 *
 * @var mysqli_result $res
 * @var array<string,mixed>|null $recipe
 */
$stmt->execute();
$res    = $stmt->get_result();
$recipe = $res->fetch_assoc();
$stmt->close();
/**
 * Handle missing or restricted recipe access.
 */
if (!$recipe) {
    http_response_code(404);
    echo 'Recipe not found or access denied.';
    exit;
}


