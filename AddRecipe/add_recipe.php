<?php
global $conn;
session_start();
require_once "../db.php";
include __DIR__ . '/../utils/IngredientsControl.php';
header("Content-Type: application/json");
const NAME_PATTERN = '~^[- A-Za-z\x{00C0}-\x{024F}\x{0400}-\x{052F}]+$~u';


const TEXT_PATTERN = '~^[A-Za-z\x{00C0}-\x{024F}\x{0400}-\x{052F}0-9+\-,.%:;()\'"*!/ \r\n]+'
    . '(,[A-Za-z\x{00C0}-\x{024F}\x{0400}-\x{052F}0-9+\-,.%:;()\'"*!/ \r\n]+)*$~u';
function ControlRecipe($name, $description, $instruction, $category)
{
    if (strlen($name) < 3 || strlen($name) > 100 || !preg_match(NAME_PATTERN, $name)) {
        echo json_encode(["status" => "error", "message" => "Invalid recipe name"]);
        exit;
    }

    if (strlen($description) < 10 || strlen($description) > 130 || !preg_match(TEXT_PATTERN, $description)) {
        echo json_encode(["status" => "error", "message" => "Invalid description"]);
        exit;
    }

    if (strlen($instruction) < 20 || strlen($instruction) > 5000 || !preg_match(TEXT_PATTERN, $instruction)) {
        echo json_encode(["status" => "error", "message" => "Invalid instruction"]);
        exit;
    }

    $validCategories = ['breakfast', 'lunch', 'dinner', 'dessert', 'snack'];
    if (!in_array($category, $validCategories, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid category"]);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;
    $auth_token = $_COOKIE['auth_token'] ?? null;
    $uploadDir = '../uploads/';

    if (!$user_id || !$auth_token) {
        echo json_encode(["status" => "error", "message" => "User not authenticated"]);
        exit;
    }
    $checkTokenQuery = "SELECT id FROM users WHERE id = ? AND auth_token = ?";
    $stmt = $conn->prepare($checkTokenQuery);
    $stmt->bind_param("is", $user_id, $auth_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $result->close();
        echo json_encode(["status" => "error", "message" => "Invalid authentication token"]);
        exit;
    }
    $stmt->close();


    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $ingredientsRaw = trim($_POST["ingredients"]);
    $ingredients = validateIngredients($ingredientsRaw);
    $instruction = trim($_POST["instruction"]);
    $category = $_POST["category"] ?? '';


    if(!isset($_FILES['recipeImage']) || $_FILES['recipeImage']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
    $image = $_FILES['recipeImage'];
    $maxSize = 5 * 1024 * 1024;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $image['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg', 'image/png','image/jpg'];
    if (!in_array($mime, $allowed, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }
    if($image['size'] > $maxSize) {
        echo json_encode(["status" => "error", "message" => "Image is too big"]);
        exit;
    }
    $imageExt = pathinfo($image['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('recipe_', true) . '.' . $imageExt;
    $imagePath = $uploadDir . $imageName;
    if(!move_uploaded_file($image['tmp_name'], $imagePath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
    ControlRecipe($name,$description,$instruction,$category);
    $addQuery = "INSERT INTO recipes (user_id, name, description, ingredients, instruction, category, image_path, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($addQuery);
    $stmt->bind_param('issssss', $user_id,$name,$description,$ingredients,$instruction,$category,$imageName);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Recipe added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
    $stmt->close();


    exit;
}