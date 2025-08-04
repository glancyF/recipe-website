<?php
global $conn;

require_once __DIR__ . '/../../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id'])){
    die('Unauthorized access!');
}
if(!isset($_GET['id'])){
    die('No recipe selected!');
}

$recipeId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $recipeId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
if(!$recipe){
    die('Recipe not found!');
}
