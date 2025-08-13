<?php
global $conn;
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/getUserIdANDToken.php';
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $auth_token = $_COOKIE["auth_token"];
    GetUserIdANDToken($conn,$user_id, $auth_token);
    $response = ["status" => "success", "message" => "Action completed"];

    $new_username = trim($_POST["username"]);
    $new_gender = $_POST["gender"] ?? "";


    if (strlen($new_username) < 3 || strlen($new_username) > 12) {
        echo json_encode(["status" => "error", "message" => "Username must be between 3 and 12 characters"]);
        exit;
    }

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
        $updateUsernameQuery = "UPDATE users SET username=? WHERE id=?";
        $stmt = $conn->prepare($updateUsernameQuery);
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        $response =["status" => "success", "new_username" => $new_username];
        $stmt->close();
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