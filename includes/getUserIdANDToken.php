<?php
function GetUserIdANDToken($conn,$user_id, $auth_token)
{
    if (!$user_id || !$auth_token) {
        echo json_encode(["status" => "error", "message" => "User not authenticated"]);
        exit;
    }
    $checkTokenQuery = "SELECT id FROM users WHERE id=? AND auth_token=?";
    $stmt = $conn->prepare($checkTokenQuery);
    $stmt->bind_param("is", $user_id,$auth_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if($result->num_rows  ===0)
    {
        echo json_encode(["status" => "error", "message" => "Invalid authentication token"]);
        exit;
    }


}