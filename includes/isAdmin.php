<?php
/**
 * Admin status check helper.
 *
 * This utility provides a simple function `isAdmin()` that determines whether
 * the currently logged-in user (from session) has administrative privileges.
 * It queries the database for the user's `status` field and compares it to `'admin'`.
 *
 * Behavior:
 * - If no user is logged in → returns false.
 * - If user exists and has `status = 'admin'` → returns true.
 * - Otherwise → returns false.
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
require_once __DIR__ .'/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Check if the currently logged-in user is an administrator.
 *
 * Validates the current session, fetches the user's `status` from the database,
 * and determines whether it equals `'admin'`.
 *
 * @global mysqli $conn Active database connection.
 *
 * @return bool True if the current user has admin privileges, false otherwise.
 */
function isAdmin(): bool {
    // --- Ensure user is logged in ---
    if (!isset($_SESSION['user_id'])) return false;
    global $conn;
    /** @var mysqli_stmt $stmt Prepared statement for user status check */
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    /** @var string|false $status User status (admin/user), or false if not found */
    $status = $stmt->get_result()->fetch_column();
    return $status === 'admin';
}