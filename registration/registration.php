<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header("Content-Type: application/json");
    session_start();
    require_once '../db.php';
    if (!isset($conn)){
        echo json_encode([
            "status" => "error",
            "message" => "Database connection not established"
        ]);
        exit;
    }

    function dataValidation($username,$password,$confirm_password,$email,$agreement,$gender){
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $username)) {
            echo json_encode([
                "status" => "error",
                "message" => "Username must start with a letter and contain only letters, numbers, underscores, or hyphens"
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
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim(strip_tags($_POST["username"]));
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        $email = trim(strip_tags($_POST["email"]));
        $gender = $_POST["gender"];
        $agreement = isset($_POST["agreement"]) ? 1 : 0;
        $status = 'user';

        dataValidation($username,$password,$confirm_password,$email,$agreement,$gender);

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


//            $updateTokenQuery = "UPDATE users SET auth_token=? WHERE id=?";
//            $updateStmt = $conn->prepare($updateTokenQuery);
//            $updateStmt->bind_param("si", $auth_token, $_SESSION["user_id"]);
//            $updateStmt->execute();
            setcookie("auth_token", $auth_token,[
                "expires" => time() + (86400 * 30),
                "path" => "/",
                //"secure" => false, //на релизе поменяю на тру
                "httponly" => true,
                "samesite" => "Lax",
            ]);
            echo json_encode(["status" => "success", "message" => "Registration successful"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Registration failed"]);
            exit;
        }

    }


?>