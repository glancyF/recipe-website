<?php
/**
 * Authentication helper functions.
 *
 * This file provides reusable utilities for session-based authentication,
 * including secure logout and authentication enforcement with cookie/session
 * validation.
 *
 * Functions:
 * - forceLogout(): Completely clears session and cookies, destroying authentication.
 * - requireAuth(): Ensures the user is authenticated; otherwise redirects to login.
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection (from ../db.php)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../db.php';
/**
 * Forcefully log out the current user.
 *
 * Clears all session data, destroys the session, and removes authentication cookies.
 * This function should be used when authentication is invalid or expired.
 *
 * @return void
 */
function forceLogout(): void {
    // Clear session array
    $_SESSION = [];
    // Remove session cookie if used
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    // Destroy the PHP session
    session_destroy();
    // Delete authentication cookie
    setcookie('auth_token', '', time() - 3600, '/', '', !empty($_SERVER['HTTPS']), true);
}
/**
 * Require user authentication for protected pages.
 *
 * Validates session and cookie authentication data against the database.
 * - If the session user is valid and matches the cookie token → returns user data.
 * - If only the cookie token is valid → reinitializes the session and returns user data.
 * - If both are invalid → destroys session/cookie and redirects to login.
 *
 * Behavior:
 * - Redirects to /login/auth.php on authentication failure.
 * - Regenerates session ID on successful cookie-based authentication.
 *
 * @global mysqli $conn Database connection
 *
 * @return array{
 *   id:int,
 *   username:string,
 *   email:string,
 *   gender:string|null,
 *   status:string,
 *   auth_token:string
 * } Authenticated user's data from the database
 *
 * @throws void Redirects immediately (no exception thrown) if authentication fails.
 */
function requireAuth(): array {
    global $conn;
    /** @var string $cookieToken Authentication token from cookie */
    $cookieToken = $_COOKIE['auth_token'] ?? '';
    /** @var int|null $sessionUserId User ID from session */
    $sessionUserId = $_SESSION['user_id'] ?? null;
    // --- Case 1: Missing authentication token ---
    if (!$cookieToken) {
        forceLogout();
        header('Location: /login/auth.php');
        exit;
    }
    // --- Case 2: Validate session user if available ---
    if ($sessionUserId) {
        /** @var mysqli_stmt $q */
        $q = $conn->prepare("SELECT id, username, email, gender, status, auth_token FROM users WHERE id=? LIMIT 1");
        $q->bind_param('i', $sessionUserId);
        $q->execute();
        $u = $q->get_result()->fetch_assoc();
        $q->close();

        if ($u && hash_equals($u['auth_token'] ?? '', $cookieToken)) {
            return $u;
        }
    }
    // --- Case 3: Validate by cookie token only ---
    $q = $conn->prepare("SELECT id, username, email, gender, status, auth_token FROM users WHERE auth_token=? LIMIT 1");
    $q->bind_param('s', $cookieToken);
    $q->execute();
    $u = $q->get_result()->fetch_assoc();
    $q->close();

    if ($u) {
        // Securely refresh session
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['status']  = $u['status'] ?? 'user';
        return $u;
    }
    // --- Case 4: Authentication failed ---
    forceLogout();
    header('Location: /login/auth.php');
    exit;
}
