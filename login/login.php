<?php
/**
 * User login endpoint (JSON API).
 *
 * Handles user authentication via email and password.
 * Validates credentials against the database, generates a new auth token
 * on successful login, and returns a JSON response indicating the result.
 *
 * Behavior:
 * - Accepts POST requests with `email` and `password` fields.
 * - Validates inputs (length, presence, basic formatting).
 * - On success:
 *   - Generates new `auth_token`.
 *   - Updates user record in the database.
 *   - Sets secure cookie `auth_token`.
 *   - Stores user data in session.
 *   - Returns JSON: `{"status":"success","message":"Login successful"}`
 * - On failure:
 *   - Returns JSON error message (invalid credentials or invalid input).
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../db.php
 */
session_start();
require_once "../db.php";
header("Content-Type: application/json");
/**
 * Check that database connection is available.
 */
if (!isset($conn)){
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not established"
    ]);
    exit;
}
/**
 * Validate user input data for login form.
 *
 * Ensures that both email and password meet basic length requirements and are not empty.
 * Emits JSON error and exits on invalid input.
 *
 * @param string $email    User's email address.
 * @param string $password User's plaintext password.
 *
 * @return void Outputs JSON error and exits on validation failure.
 */
function dataValidation($email,$password)
{
    if (strlen($password) < 8 || strlen($password) > 16) {
        echo json_encode(["status" => "error", "message" => "Password must be between 8 and 16 characters"]);
        exit;
    }
    if (strlen($email) < 2 || strlen($email) > 64) {
        echo json_encode(["status" => "error", "message" => "Email must be between 2 and 64 characters"]);
        exit;
    }
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }
    if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$password)) {
        echo json_encode([
            "status" => "error",
            "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
        ]);
        exit;
    }
}
/**
 * Handle POST request for user authentication.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /** @var string $email Sanitized user email */
    $email = trim(strip_tags($_POST["email"]));
    /** @var string $password Raw user password */
    $password = trim($_POST["password"]);
    // Validate email and password
    dataValidation($email,$password);
    /** @var string $query SQL query to fetch user record */
    $query = "SELECT id,email,password,status FROM users WHERE email=?";
    /** @var mysqli_stmt $stmt Prepared statement for user lookup */
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    /** @var mysqli_result $result Result set from user query */
    $result = $stmt->get_result();
    /** @var array<string,mixed>|null $user User record from database (or null if not found) */
    $user = $result->fetch_assoc();
    // --- Verify password ---
    if($user && password_verify($password,$user["password"])){
        /** @var string $auth_token Newly generated authentication token */
        $auth_token = bin2hex(random_bytes(32));
        // Update token in DB
        $updateQuery = "UPDATE users SET auth_token=? WHERE id=?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $auth_token,$user["id"]);
        $updateStmt->execute();
        // Set secure cookie
        setcookie("auth_token",$auth_token,[
            "expires" => time() + (86400 * 30), // 30 days
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        // Initialize session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['status'] = $user['status'];
        // Respond success
        echo json_encode(["status" => "success", "message" => "Login successful"]);
        exit;
    }
    else {
        // --- Invalid credentials ---
        echo json_encode(["status" => "error", "message" => "Invalid credentials or account doesn't exist"]);
        exit;
    }

}
