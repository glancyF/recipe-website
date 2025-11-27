<?php
/**
 * User registration endpoint (JSON API).
 *
 * Handles user signup by validating inputs, enforcing uniqueness for username
 * and email, hashing the password, creating a new user record, initializing
 * the session, issuing an `auth_token` cookie, and returning a JSON response.
 *
 * Behavior:
 * - Method: POST only.
 * - Validates: username, password, confirm_password, email, gender, agreement.
 * - Enforces uniqueness for both username and email.
 * - On success:
 *   - Inserts user with `status = 'user'` and a fresh `auth_token`.
 *   - Sets secure HttpOnly cookie `auth_token` (SameSite=Lax).
 *   - Initializes session (username, email, gender, agreement, user_id, status).
 *   - Returns `{"status":"success","message":"Registration successful"}`.
 * - On error: returns `{"status":"error","message":"..."}` with details.
 *
 * Security:
 * - Passwords hashed with `password_hash()` (default algorithm).
 * - Uses prepared statements for all DB queries.
 * - Returns JSON; does not expose DB errors to clients.
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
session_start();
require_once '../db.php';

/**
 * Ensure DB connection is available.
 */
if (!isset($conn)){
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not established"
    ]);
    exit;
}
/**
 * Validate registration input data.
 *
 * Emits a JSON error and exits on validation failure.
 *
 * @param string     $username          Desired username (3–12; starts with letter; letters/digits/_/-).
 * @param string     $password          Plaintext password (8–16).
 * @param string     $confirm_password  Confirmation of password (must match).
 * @param string     $email             Email address (2–64; valid format).
 * @param int|bool   $agreement         Consent/terms agreement (expected truthy).
 * @param string     $gender            One of "Male" or "Female".
 *
 * @return void
 */
function dataValidation($username,$password,$confirm_password,$email,$agreement,$gender){
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $username)) {
        echo json_encode([
            "status" => "error",
            "message" => "Username must start with a letter and contain only letters, numbers, underscores, or hyphens. English letters only"
        ]);
        exit;
    }
    if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$password)) {
        echo json_encode([
            "status" => "error",
            "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
        ]);
        exit;
    }
    if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$confirm_password)) {
        echo json_encode([
            "status" => "error",
            "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }
    if(empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($gender) || empty($agreement)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }
    if ($password != $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }
    if (!in_array($gender, array("Male", "Female"))) {
        echo json_encode(["status" => "error", "message" => "Please select a gender"]);
        exit;
    }
    if (strlen($username) < 3 || strlen($username) > 12) {
        echo json_encode(["status" => "error", "message" => "Username must be between 3 and 12 characters"]);
        exit;
    }
    if (strlen($password) < 8 || strlen($password) > 16) {
        echo json_encode(["status" => "error", "message" => "Password must be between 8 and 16 characters"]);
        exit;
    }
    if (strlen($email) < 2 || strlen($email) > 64) {
        echo json_encode(["status" => "error", "message" => "Email must be between 2 and 64 characters"]);
        exit;
    }
}
/**
 * Handle registration POST.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    /** @var string $username */
    $username = trim(strip_tags($_POST["username"]));
    /** @var string $password */
    $password = trim($_POST["password"]);
    /** @var string $confirm_password */
    $confirm_password = trim($_POST["confirm_password"]);
    /** @var string $email */
    $email = trim(strip_tags($_POST["email"]));
    /** @var string $gender */
    $gender = $_POST["gender"];
    /** @var int $agreement */
    $agreement = isset($_POST["agreement"]) ? 1 : 0;
    /** @var string $status */
    $status = 'user';
    // Validate input fields (exits on failure)
    dataValidation($username,$password,$confirm_password,$email,$agreement,$gender);
    // Hash password
    /** @var string $hashed_password */
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $checkUserQuery = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username already exists"]);
        $stmt->close();
        exit;
    }

    $stmt->close();
    $checkEmailQuery = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        $stmt->close();
        exit;
    }
    $stmt->close();
    $status = 'user';
    /** @var string $auth_token */
    $auth_token = bin2hex(random_bytes(32));
    $insertUserQuery = "INSERT INTO users (username, password, email, gender, agreement, status, auth_token) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertUserQuery);
    $stmt->bind_param("sssssss", $username, $hashed_password, $email, $gender, $agreement, $status, $auth_token);
    if($stmt->execute()){
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["gender"] = $gender;
        $_SESSION["agreement"] = $agreement;
        $_SESSION["user_id"] = $conn->insert_id;
        $_SESSION['status'] = 'user';

        setcookie("auth_token", $auth_token,[
            "expires" => time() + (86400 * 30),
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        echo json_encode(["status" => "success", "message" => "Registration successful"]);
        exit;
    } else {
        // Insert failed
        echo json_encode(["status" => "error", "message" => "Registration failed"]);
        exit;
    }

}

