<?php
session_start();
require_once "../db.php";
header("Content-Type: application/json");
const PASSWORD_PATTERN = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}$/';
if (!isset($conn)){
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not established"
    ]);
    exit;
}
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
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Please enter a valid email address"]);
        exit;
    }

    if (!preg_match(PASSWORD_PATTERN, $password)) {
        echo json_encode([
            "status" => "error",
            "message" => "The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter"
        ]);
        exit;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(strip_tags($_POST["email"]));
    $password = trim($_POST["password"]);
    dataValidation($email,$password);
    $query = "SELECT id,email,password,status FROM users WHERE email=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if($user && password_verify($password,$user["password"])){
        $auth_token = bin2hex(random_bytes(32));
        $updateQuery = "UPDATE users SET auth_token=? WHERE id=?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $auth_token,$user["id"]);
        $updateStmt->execute();
        setcookie("auth_token",$auth_token,[
            "expires" => time() + (86400 * 30),
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['status'] = $user['status'];

        echo json_encode(["status" => "success", "message" => "Login successful"]);
        exit;
    }
    else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials or account doesn't exist"]);
        exit;
    }

}
