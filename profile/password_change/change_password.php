<?php
global $conn;
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/getUserIdANDToken.php';
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $auth_token = $_COOKIE['auth_token'];
    GetUserIdANDToken($conn,$user_id, $auth_token);
    $response = ["status" => "success", "message" => "Action completed"];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_new_password']);
    if (strlen($current_password) < 8 || strlen($current_password) > 16 || strlen($confirm_password) < 8 || strlen($confirm_password) > 16 || strlen($new_password) < 8 || strlen($new_password) > 16) {
        echo json_encode(["status" => "error", "message" => "Password must be between 8 and 16 characters"]);
        exit;
    }
    if ($new_password != $confirm_password) {
        echo json_encode(["status" => "error", "message" => "New password and confirm password do not match"]);
        exit;
    }
    if(!empty($new_password) && !empty($confirm_password) && !empty($current_password)){
     $query = "SELECT password FROM users WHERE id = ?";
     $stmt = $conn->prepare($query);
     $stmt->bind_param("i", $user_id);
     $stmt->execute();
     $result = $stmt->get_result();
     $stmt->close();
     $user = $result->fetch_assoc();
     if (!password_verify($current_password, $user['password'])) {
         echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
         exit;
     }
     $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
     $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
     $stmt = $conn->prepare($updatePasswordQuery);
     $stmt->bind_param("si", $hashed_password, $user_id);
     $stmt->execute();
     $stmt->close();
     $response =["status" => "success", "new_password" => $new_password];
     echo json_encode($response);
     exit;
    }
}