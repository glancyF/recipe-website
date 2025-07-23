<?php
global $conn;
session_start();
require_once '../db.php';
if (isset($_COOKIE['auth_token'])) {
    $auth_token = $_COOKIE['auth_token'];
    $query = "UPDATE users SET auth_token = NULL WHERE auth_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $auth_token);
    $stmt->execute();
    setcookie('auth_token', "", time() -3600,'/',"",true,true);
}

session_unset();
session_destroy();


header("Location: ../login/auth.php");
exit;