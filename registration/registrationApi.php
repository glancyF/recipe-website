<?php
/**
 * Registration form handler (server-rendered flow).
 *
 * Processes the HTML registration form, validates inputs, checks uniqueness
 * for username and email, creates the user, initializes session, issues an
 * auth cookie, and redirects accordingly. On validation or DB errors, stores
 * flash data in the session and redirects back to the registration page.
 *
 * Behavior:
 * - Method: POST expected; otherwise redirects to /registration/register.php
 * - Validates:
 *   - username: /^[A-Za-z][A-Za-z0-9_-]*$/ and length 3–12
 *   - email: valid format and length 2–64
 *   - password: 8–16 chars, must include at least one digit, one lowercase, one uppercase
 *   - confirm_password: must match password
 *   - gender: one of {"Male","Female"}
 *   - agreement: required checkbox
 * - Uniqueness checks: username, email
 * - On success: inserts user, sets session, sets HttpOnly auth cookie, redirects to /main/main.php
 * - On failure: sets `$_SESSION['flash_register']` with errors and old values, redirects back
 *
 * Flash structure (`$_SESSION['flash_register']`):
 * ```php
 * [
 *   'old'    => ['username' => '...', 'email' => '...', 'gender' => 'Male', 'agreement' => 1],
 *   'errors' => ['username' => 'Username already exists', ...],
 *   'general'=> 'Please fix the errors below'
 * ]
 * ```
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */

session_start();
require_once '../db.php';
/** @var mysqli $conn */
global $conn;
/** @var array<string,mixed> $old Previously submitted values for repopulation */
$old = [];
/** @var array<string,string> $errors Field validation errors */
$errors = [];
/** @var string|null $general General error message (optional) */
$general = null;
/**
 * Reject non-POST requests by redirecting to the registration page.
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /registration/register.php');
}
/**
 * Collect raw inputs.
 *
 * @var string $username
 * @var string $password
 * @var string $confirm_password
 * @var string $email
 * @var string $gender
 * @var int    $agreement
 */
$username         = trim($_POST['username'] ?? '');
$password         = (string)($_POST['password'] ?? '');
$confirm_password = (string)($_POST['confirm_password'] ?? '');
$email            = trim($_POST['email'] ?? '');
$gender           = $_POST['gender'] ?? '';
$agreement        = isset($_POST['agreement']) ? 1 : 0;
/** Preserve submitted values (except passwords) */
$old = [
    'username'  => $username,
    'email'     => $email,
    'gender'    => $gender,
    'agreement' => $agreement,
];

if ($username === '' || !preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $username)) {
    $errors['username'] = 'Invalid username format';
}
if (strlen($username) < 3 || strlen($username) > 12) {
    $errors['username'] = 'Username must be between 3 and 12 characters';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}
if (strlen($email) < 2 || strlen($email) > 64) {
    $errors['email'] = 'Email must be between 2 and 64 characters';
}
if ($password === '' || strlen($password) < 8 || strlen($password) > 16) {
    $errors['password'] = 'Password must be 8-16 chars';
}
elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$password)) {
    $errors['password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
elseif (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$confirm_password)) {
    $errors['confirm_password'] = "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only";
}
if ($confirm_password === '' || $confirm_password !== $password) {
    $errors['confirm_password'] = "Passwords don't match";
}

if (!in_array($gender, ['Male','Female'], true)) {
    $errors['gender'] = 'Please select a gender';
}
if (!$agreement) {
    $errors['agreement'] = 'You must agree to continue';
}

if (empty($errors)) {
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors['username'] = 'Username already exists';
    $stmt->close();
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['email'] = 'Email already exists';
        $stmt->close();
    }
}
if (!empty($errors)) {
    $_SESSION['flash_register'] = [
        'old'    => $old,
        'errors' => $errors,
        'general'=> 'Please fix the errors below',
    ];
    header('Location: /registration/register.php');
    exit;
}
/** @var string $hashed_password */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
/** @var string $status */
$status = 'user';
/** @var string $auth_token */
$auth_token = bin2hex(random_bytes(32));

$stmt = $conn->prepare("INSERT INTO users (username,password,email,gender,agreement,status,auth_token)
                        VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("ssssiss", $username, $hashed_password, $email, $gender, $agreement, $status, $auth_token);

if ($stmt->execute()) {
    $_SESSION["username"]  = $username;
    $_SESSION["email"]     = $email;
    $_SESSION["gender"]    = $gender;
    $_SESSION["agreement"] = $agreement;
    $_SESSION["user_id"]   = $conn->insert_id;
    $_SESSION['status']    = 'user';

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie('auth_token', $auth_token, [
        'expires'  => time() + 86400*30,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    header('Location: /main/main.php');
    exit;
} else {
    $_SESSION['flash_register'] = [
        'old'    => $old,
        'errors' => ['general' => 'Database error'],
        'general'=> 'Database error: '.$stmt->error,
    ];
    header('Location: /registration/register.php');
    exit;
}