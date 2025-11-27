<?php
/**
 * Handle password change form submission (HTML version).
 *
 * This script processes the user’s password change request from the profile page.
 * It performs full validation, verifies the current password, updates it in the database,
 * and redirects the user with appropriate flash messages.
 *
 * Behavior:
 * - Accepts only POST requests.
 * - Requires an active session and valid `auth_token` cookie.
 * - Validates password fields for presence, length (8–16 chars), and matching.
 * - Verifies the current password before updating.
 * - On success, updates the stored hash and redirects back to the password change section.
 * - On validation or authentication failure, sets session flash errors and redirects back.
 *
 * Example flash errors stored in session:
 * ```php
 * $_SESSION['flash_change_pass'] = [
 *   'errors' => [
 *     'current_password' => 'Current password is incorrect',
 *     'new_password' => 'Password must be 8–16 chars',
 *     'confirm_new_password' => 'New password and confirm password do not match'
 *   ],
 *   'general' => 'Please fix the errors below'
 * ];
 * ```
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/getUserIdANDToken.php';
global $conn;
/**
 * Reject non-POST requests to prevent direct access.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile/profile.php?section=change_pass'); exit;
}
/** @var array<string,string> $errors Validation error messages */
$errors = [];

/**
 * Authentication: ensure session and cookie token are valid.
 *
 * @var int|null $user_id
 * @var string|null $auth_token
 */
$user_id    = $_SESSION['user_id'] ?? null;
$auth_token = $_COOKIE['auth_token'] ?? null;
GetUserIdANDToken($conn, $user_id, $auth_token);

/**
 * Collect and sanitize input fields.
 *
 * @var string $current Current password (plaintext input)
 * @var string $new New desired password
 * @var string $confirm Confirmation of new password
 */
$current = (string)($_POST['current_password'] ?? '');
$new     = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_new_password'] ?? '');


$lenBad = fn($s) => (strlen($s) < 8 || strlen($s) > 16);
/**
 * Validate all password fields.
 */
if ($current === '') $errors['current_password'] = 'Please enter your current password';
elseif ($lenBad($current)) $errors['current_password'] = 'Password must be 8–16 chars';

if ($new === '') $errors['new_password'] = 'Please enter a new password';
elseif ($lenBad($new)) $errors['new_password'] = 'Password must be 8–16 chars';

if ($confirm === '') $errors['confirm_new_password'] = 'Please confirm your new password';
elseif ($lenBad($confirm)) $errors['confirm_new_password'] = 'Password must be 8–16 chars';


elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$current)) {
    $errors['password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$new)){
    $errors['new_password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$confirm)){
    $errors['confirm_new_password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
if ($new !== '' && $confirm !== '' && $new !== $confirm) {
    $errors['confirm_new_password'] = "New password and confirm password do not match";
}
/**
 * Verify current password if no validation errors so far.
 */
if (!$errors) {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current, $row['password'])) {
        $errors['current_password'] = 'Current password is incorrect';
    }
}
/**
 * Handle validation or verification errors.
 */
if ($errors) {
    $_SESSION['flash_change_pass'] = [
        'errors'  => $errors,
        'general' => 'Please fix the errors below'
    ];
    header('Location: /profile/profile.php?section=change_pass');
    exit;
}
/**
 * Hash the new password and update in database.
 */
$hashed = password_hash($new, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->bind_param("si", $hashed, $user_id);
$stmt->execute();
$stmt->close();
/**
 * Redirect back to profile after successful change.
 */
header('Location: /profile/profile.php?section=change_pass');
exit;
