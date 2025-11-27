<?php
/**
 * Change user password (authenticated API endpoint).
 *
 * This endpoint allows an authenticated user to securely change their password.
 * It validates the current password, ensures the new password meets complexity
 * and length requirements, and updates the password hash in the database.
 *
 * Behavior:
 * - Accepts only POST requests.
 * - Requires an active session and a valid `auth_token` cookie.
 * - Validates all password fields (8–16 chars, match check, etc.).
 * - Returns JSON indicating success or detailed error.
 *
 * Security:
 * - Uses `GetUserIdANDToken()` to validate authentication.
 * - Verifies current password before updating.
 * - Hashes the new password using `password_hash()` with the default algorithm.
 *
 * Example success response:
 * ```json
 * { "status": "success", "message": "Action completed" }
 * ```
 *
 * Example error responses:
 * ```json
 * { "status": "error", "message": "Current password is incorrect" }
 * { "status": "error", "message": "Password must be between 8 and 16 characters" }
 * { "status": "error", "message": "New password and confirm password do not match" }
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/getUserIdANDToken.php';
header("Content-Type: application/json");
/**
 * Handle password change request.
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /** @var int $user_id Authenticated user's ID from session */
    $user_id = $_SESSION['user_id'];
    /** @var string $auth_token Authentication token from secure cookie */
    $auth_token = $_COOKIE['auth_token'];
    // --- Validate authentication token pair (session + cookie) ---
    GetUserIdANDToken($conn,$user_id, $auth_token);
    $response = ["status" => "success", "message" => "Action completed"];
    /** @var string $current_password Current user password (plaintext input) */
    $current_password = trim($_POST['current_password']);
    /** @var string $new_password New desired password (plaintext input) */
    $new_password = trim($_POST['new_password']);
    /** @var string $confirm_password Repeated new password (plaintext input) */
    $confirm_password = trim($_POST['confirm_new_password']);
    /**
     * Validate password length (8–16 characters for all fields).
     */
    if (strlen($current_password) < 8 || strlen($current_password) > 16 || strlen($confirm_password) < 8 || strlen($confirm_password) > 16 || strlen($new_password) < 8 || strlen($new_password) > 16) {
        echo json_encode(["status" => "error", "message" => "Password must be between 8 and 16 characters"]);
        exit;
    }
    if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$current_password) || !preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$confirm_password) || !preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/",$new_password)) {
        echo json_encode([
            "status" => "error",
            "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
        ]);
        exit;
    }
    /**
     * Ensure new password and confirmation match.
     */
    if ($new_password != $confirm_password) {
        echo json_encode(["status" => "error", "message" => "New password and confirm password do not match"]);
        exit;
    }
    /**
     * Proceed only if all fields are filled and valid.
     */
    if(!empty($new_password) && !empty($confirm_password) && !empty($current_password)){
     $query = "SELECT password FROM users WHERE id = ?";
     $stmt = $conn->prepare($query);
     $stmt->bind_param("i", $user_id);
     $stmt->execute();
     $result = $stmt->get_result();
     $stmt->close();
     /** @var array{password:string}|null $user */
     $user = $result->fetch_assoc();
     // --- Verify provided current password ---
     if (!password_verify($current_password, $user['password'])) {
         echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
         exit;
     }
     // --- Hash new password securely ---
     $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // --- Update password in database ---
     $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
     $stmt = $conn->prepare($updatePasswordQuery);
     $stmt->bind_param("si", $hashed_password, $user_id);
     $stmt->execute();
     $stmt->close();
     $response =["status" => "success"];
     // --- Success response ---
     echo json_encode($response);
     exit;
    }
}