<?php
/**
 * Validate user authentication via ID and token.
 *
 * This helper function checks whether a given user ID and authentication token
 * correspond to a valid record in the `users` table.
 * It is intended for use in API endpoints that require an authenticated user.
 *
 * Behavior:
 * - If either `$user_id` or `$auth_token` is missing → responds with JSON error and terminates script.
 * - If the provided token does not match the user → responds with JSON error and terminates script.
 * - If the credentials are valid → simply returns (no output).
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @param mysqli $conn       Active MySQLi connection object.
 * @param int|null $user_id  User ID from session or request.
 * @param string|null $auth_token Authentication token from cookie or header.
 *
 * @return void Outputs JSON error and exits on failure; returns silently on success.
 *
 * @throws void The function terminates execution via `exit()` on invalid credentials.
 */
function GetUserIdANDToken($conn,$user_id, $auth_token)
{
    // --- Check that authentication data is provided ---
    if (!$user_id || !$auth_token) {
        echo json_encode(["status" => "error", "message" => "User not authenticated"]);
        exit;
    }
    // --- Prepare and execute token verification query ---
    $checkTokenQuery = "SELECT id FROM users WHERE id=? AND auth_token=?";
    $stmt = $conn->prepare($checkTokenQuery);
    $stmt->bind_param("is", $user_id,$auth_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    // --- Validate result ---
    if($result->num_rows  ===0)
    {
        echo json_encode(["status" => "error", "message" => "Invalid authentication token"]);
        exit;
    }


}