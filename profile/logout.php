<?php
/**
 * Logout script — destroys session and invalidates auth token.
 *
 * This endpoint logs out the current user by:
 * 1. Checking for an `auth_token` cookie.
 * 2. Nullifying the corresponding token in the `users` table.
 * 3. Removing the cookie from the browser.
 * 4. Clearing and destroying the session.
 * 5. Redirecting to the login page.
 *
 * Behavior:
 * - Method: GET or any (idempotent).
 * - If the cookie is missing, it simply clears session and redirects.
 * - The token is invalidated in the database to prevent reuse.
 * - Cookie is removed using secure & HttpOnly flags.
 *
 * Example redirect:
 * ```
 * Location: ../login/auth.php
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */

global $conn;
session_start();
require_once '../db.php';
/**
 * If a valid authentication cookie is set — revoke it.
 *
 * @var string|null $auth_token
 */
if (isset($_COOKIE['auth_token'])) {
    $auth_token = $_COOKIE['auth_token'];
    $query = "UPDATE users SET auth_token = NULL WHERE auth_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $auth_token);
    $stmt->execute();
    setcookie('auth_token', "", time() -3600,'/',"",true,true);
}

session_unset();
session_destroy();


header("Location: ../login/auth.php");
exit;