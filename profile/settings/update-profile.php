<?php
/**
 * Update profile: username (required) and optionally gender.
 *
 * Authenticated JSON endpoint that updates the current user's profile fields.
 * It validates the session + auth token pair, enforces username length and
 * uniqueness, updates the username, and if a non-empty `gender` is provided,
 * updates the gender as well.
 *
 * Behavior:
 * - Method: POST only.
 * - Auth: requires valid session `user_id` and cookie `auth_token`
 *         verified by `GetUserIdANDToken()`.
 * - Validation:
 *   - `username`: 3–12 characters; must be unique across users (excluding self).
 * - On success:
 *   - If username updated → `{"status":"success","new_username":"..."}`
 *   - If gender also updated → `{"status":"success","new_gender":"..."}`
 * - On errors:
 *   - `{"status":"error","message":"Username must be between 3 and 12 characters"}`
 *   - `{"status":"error","message":"Username already taken"}`
 *
 * Notes:
 * - The endpoint only proceeds when `username` is non-empty (it is treated as required).
 * - `gender` update is optional and performed only when a non-empty value is provided.
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
header("Content-Type: application/json");
/**
 * Handle POST request to update username (and optionally gender).
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    /** @var int $user_id Current authenticated user ID */
    $user_id = $_SESSION["user_id"];
    /** @var string $auth_token Authentication token from cookie */
    $auth_token = $_COOKIE["auth_token"];
    // Validate authentication pair (exits with JSON error on failure)
    GetUserIdANDToken($conn,$user_id, $auth_token);
    /** @var array<string,string> $response Base success payload */
    $response = ["status" => "success", "message" => "Action completed"];
    /** @var string $new_username Desired new username */
    $new_username = trim($_POST["username"]);
    /** @var string $new_gender Optional gender value */
    $new_gender = $_POST["gender"] ?? "";

    // --- Username length validation ---
    if (strlen($new_username) < 3 || strlen($new_username) > 12) {
        echo json_encode(["status" => "error", "message" => "Username must be between 3 and 12 characters"]);
        exit;
    }
    // Only proceed if username is not empty
    if (!empty($new_username)) {
        $checkQuery = "SELECT id FROM users WHERE username=? AND id != ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows  >0){

            $stmt->close();
            echo json_encode(["status" => "error", "message" => "Username already taken"]);
            exit;
        }

        $stmt->close();
        // --- Update username ---
        $updateUsernameQuery = "UPDATE users SET username=? WHERE id=?";
        $stmt = $conn->prepare($updateUsernameQuery);
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $response =["status" => "success", "new_username" => $new_username];
        $stmt->close();
        // --- Optional gender update when provided (non-empty) ---
        if(!empty($new_gender)){
            $checkQuery = "SELECT id FROM users WHERE gender=? AND id != ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("si", $new_gender, $user_id);
            $stmt->execute();
            $stmt->close();

            $updateGenderQuery = "UPDATE users SET gender=? WHERE id=?";
            $stmt = $conn->prepare($updateGenderQuery);
            $stmt->bind_param("si", $new_gender, $user_id);
            $stmt->execute();
            $response =["status" => "success", "new_gender" => $new_gender];

            $stmt->close();
        }

        echo json_encode($response);
        exit;
    }
}