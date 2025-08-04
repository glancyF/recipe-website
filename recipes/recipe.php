<?php
global $conn;
require_once __DIR__ . '/../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('No valid recipe selected!');
}

$recipeId = (int)$_GET['id'];

$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
if(!$stmt){
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $recipeId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if(!$recipe){
    die('Recipe not found!');
}
$stmtLikes = $conn->prepare("SELECT COUNT(*) as total FROM recipe_likes WHERE recipe_id = ?");
$stmtLikes->bind_param("i", $recipeId);
$stmtLikes->execute();
$likeRow = $stmtLikes->get_result()->fetch_assoc();
$recipe['like_count'] = (int)$likeRow['total'];

// Проверка, лайкнул ли текущий пользователь
$recipe['liked'] = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ?");
    $stmtCheck->bind_param("ii", $userId, $recipeId);
    $stmtCheck->execute();
    $recipe['liked'] = $stmtCheck->get_result()->num_rows > 0;
}

$ingredients = array_filter(array_map('trim', explode(';', $recipe['ingredients'])));