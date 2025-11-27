<?php
/**
 * Admin API endpoint: Change user status (promote/demote).
 *
 * This endpoint allows administrators to change a user's `status`
 * between `user` and `admin`.
 * It is protected by both authentication and CSRF validation.
 *
 * Behavior:
 * - Only accessible to admin users (403 Forbidden if not admin).
 * - Requires a valid CSRF token in POST data.
 * - Prevents administrators from changing their own status.
 * - Returns JSON with operation result.
 *
 * Security:
 * - Requires valid session.
 * - Requires CSRF token validation.
 * - Restricts `status` to allowed values (`user`, `admin`).
 *
 * Example JSON responses:
 * ✅ Success:
 * ```json
 * {
 *   "status": "success"
 * }
 * ```
 * ❌ Invalid CSRF:
 * ```json
 * {
 *   "status": "error",
 *   "message": "Bad CSRF"
 * }
 * ```
 * ❌ Not admin:
 * ```json
 * {
 *   "status": "error",
 *   "message": "Forbidden"
 * }
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
require_once __DIR__ . '/../../includes/isAdmin.php';
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
/**
 * Ensure session is started.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Restrict access to admin users only.
 */
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Forbidden']);
    exit;
}
/**
 * CSRF validation.
 *
 * @var string $csrf Submitted CSRF token.
 */
$csrf = $_POST['csrf'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Bad CSRF']);
    exit;
}
/**
 * Input parameters.
 *
 * @var int    $userId Target user ID.
 * @var string $status Desired status ("user" or "admin").
 */
$userId = (int)($_POST['user_id'] ?? 0);
$status = $_POST['status'] ?? 'user';
/**
 * Validate provided status value.
 */
if (!in_array($status, ['user','admin'], true)) {
    echo json_encode(['status'=>'error','message'=>'Invalid status']);
    exit;
}
/**
 * Prevent admin from changing their own role.
 */
if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
    echo json_encode(['status'=>'error','message'=>"You can't change your own status"]);
    exit;
}
/**
 * Perform status update in database.
 *
 * @var mysqli_stmt $stmt
 * @var bool $ok
 */
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $userId);
$ok = $stmt->execute();
$stmt->close();
/**
 * Send JSON response.
 */
if ($ok) {
    echo json_encode(['status'=>'success']);
} else {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'DB error']);
}
exit;
