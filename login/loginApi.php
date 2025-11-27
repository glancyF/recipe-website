<?php
/**
 * Login form handler (server-rendered flow).
 *
 * Processes a POSTed login form, validates inputs, verifies credentials against the
 * database, issues a new `auth_token` cookie on success, initializes the session,
 * and redirects accordingly. On validation or credential errors, stores flash data
 * in the session and redirects back to the login page.
 *
 * Behavior:
 * - Non-POST requests are redirected to /login/auth.php
 * - Validates `email` (length, format) and `password` (length)
 * - On invalid input → sets `$_SESSION['flash_login']` and redirects to /login/auth.php
 * - On invalid credentials → sets `$_SESSION['flash_login']` and redirects to /login/auth.php
 * - On success → updates `auth_token`, sets secure cookie, sets session vars, redirects to /main/main.php
 *
 * Flash structure (`$_SESSION['flash_login']`):
 * - old:    array of previous form values (e.g., ['email' => '...'])
 * - errors: array of field-specific errors (e.g., ['email' => '...'])
 * - general: general message for the form header
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
session_start();
require_once "../db.php";
/** @global mysqli $conn */
global $conn;
/**
 * Reject non-POST requests (direct access or wrong method).
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /login/auth.php");
    exit;
}
/** @var array<string,string> $errors Field-level validation errors */
$errors = [];
/** @var array<string,string> $old Previous form values for repopulation */
$old = [];
/**
 * Raw inputs (sanitized where applicable).
 *
 * @var string $email
 * @var string $password
 */
$email    = trim(strip_tags($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$old['email'] = $email;
/* -------------------- Input validation -------------------- */
if ($email === '')                    $errors['email'] = 'Please enter your email';
elseif (strlen($email) < 2 || strlen($email) > 64) $errors['email'] = 'Email must be between 2 and 64 characters';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address';
if ($password === '')                 $errors['password'] = 'Please enter your password';
elseif (strlen($password) < 8 || strlen($password) > 16) {
    $errors['password'] = 'Password must be between 8 and 16 characters';
}
elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$password)) {
    $errors['password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
if (!empty($errors)) {
    $_SESSION['flash_login'] = [
        'old'    => $old,
        'errors' => $errors,
        'general'=> 'Please fix the errors below',
    ];
    header("Location: /login/auth.php");
    exit;
}

/* -------------------- User lookup & password verify -------------------- */
/** @var mysqli_stmt $stmt */
$stmt = $conn->prepare("SELECT id,email,password,status FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
/** @var array<string,mixed>|null $user */
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['flash_login'] = [
        'old'    => $old,
        'errors' => ['email' => "Invalid credentials or account doesn't exist"],
        'email'=> "Invalid credentials or account doesn't exist",
    ];
    header("Location: /login/auth.php");
    exit;
}
/* -------------------- Token issue & cookie setup -------------------- */
/** @var string $auth_token New authentication token (hex) */
$auth_token = bin2hex(random_bytes(32));
/** @var mysqli_stmt $upd */
$upd = $conn->prepare("UPDATE users SET auth_token=? WHERE id=?");
$upd->bind_param("si", $auth_token, $user['id']);
$upd->execute();
$upd->close();

/**
 * Determine cookie `secure` flag based on HTTPS.
 *
 * @var bool $secure
 */
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
/* Set auth cookie (HttpOnly, SameSite=Lax, 30 days) */
setcookie('auth_token', $auth_token, [
    'expires'  => time() + 86400*30,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
/* -------------------- Session initialization -------------------- */
$_SESSION['user_id'] = $user['id'];
$_SESSION['email']   = $user['email'];
$_SESSION['status']  = $user['status'];
/* -------------------- Redirect to the application home -------------------- */
header("Location: /main/main.php");
exit;