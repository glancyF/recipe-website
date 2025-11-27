<?php
/**
 * Profile settings form handler (username & gender).
 *
 * Processes updates to the authenticated user's profile settings submitted
 * from the HTML form. Validates authentication via `GetUserIdANDToken()`,
 * performs server-side validation for username and gender, writes changes
 * to the database, and redirects with flash messages on errors.
 *
 * Behavior:
 * - Method: POST only (otherwise redirect to /profile/profile.php?section=settings).
 * - Auth: requires valid session user and cookie auth token (verified).
 * - Validation:
 *   - username (optional): 3–12 chars, /^[A-Za-z][A-Za-z0-9_-]*$/; unique across users.
 *   - gender   (optional): one of {"Male","Female"}.
 * - On validation errors: stores `$_SESSION['flash_settings']` and redirects back.
 * - On success: updates provided fields (only non-empty) and redirects to profile.
 *
 * Flash example:
 * ```php
 * $_SESSION['flash_settings'] = [
 *   'old'    => ['username' => 'john_doe', 'gender' => 'Male'],
 *   'errors' => ['username' => 'Username already taken'],
 *   'general'=> 'Please fix the errors below'
 * ];
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @uses GetUserIdANDToken(mysqli $conn, int|null $user_id, string|null $auth_token): void
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/getUserIdANDToken.php';
/**
 * Reject non-POST requests and return to settings screen.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile/profile.php?section=settings');
    exit;
}
/**
 * Authentication: verify session + cookie token pair.
 *
 * @var int|null    $user_id
 * @var string|null $auth_token
 */
$user_id = $_SESSION['user_id'] ?? null;
$auth_token = $_COOKIE['auth_token'] ?? null;
GetUserIdANDToken($conn, $user_id, $auth_token);

/** @var array<string,string> $old    Previous values for flash repopulation */
/** @var array<string,string> $errors Validation errors */
$old = [];
$errors = [];
/**
 * Incoming fields (both optional).
 *
 * @var string $new_username
 * @var string $new_gender
 */
$new_username = trim($_POST['username'] ?? '');
$new_gender = $_POST['gender'] ?? '';

$old['username'] = $new_username;
$old['gender'] = $new_gender;
/* -------------------- Validation -------------------- */
if ($new_username !== '') {
    // Username: start with letter; letters/digits/_/-; 3–12 chars
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $new_username) || strlen($new_username) < 3 || strlen($new_username) > 12) {
        $errors['username'] = 'Invalid username (3–12, letters/digits/_/-, start with letter)';
    } else {
        // Uniqueness (exclude self)
        $stmt = $conn->prepare("SELECT 1 FROM users WHERE username=? AND id<>? LIMIT 1");
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['username'] = 'Username already taken';
        $stmt->close();
    }
}
// Gender whitelist (optional field)
if ($new_gender !== '' && !in_array($new_gender, ['Male', 'Female'], true)) {
    $errors['gender'] = 'Please select a valid gender';
}
/* -------------------- Error handling (flash + redirect) -------------------- */
if ($errors) {
    $_SESSION['flash_settings'] = ['old' => $old, 'errors' => $errors, 'general' => 'Please fix the errors below'];
    header('Location: /profile/profile.php?section=settings');
    exit;
}
/* -------------------- Apply updates (only non-empty fields) -------------------- */
if ($new_username !== '') {
    $stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    $stmt->close();
}
if ($new_gender !== '') {
    $stmt = $conn->prepare("UPDATE users SET gender=? WHERE id=?");
    $stmt->bind_param("si", $new_gender, $user_id);
    $stmt->execute();
    $stmt->close();
}
/* -------------------- Success redirect -------------------- */
header('Location: /profile/profile.php');
exit;
