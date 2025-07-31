<?php
global $conn;
require_once __DIR__ . '/../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('No valid recipe selected!');
}

$recipeId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? ");
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
$ingredients = array_filter(array_map('trim', explode(';', $recipe['ingredients'])));