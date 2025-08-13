<?php
declare(strict_types=1);
global $conn;
include __DIR__ . '/../../utils/IngredientsControl.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';
const NAME_PATTERN = '/^[A-Za-z\s\-]+$/';
const TEXT_PATTERN = '/^[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+(,[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+)*$/';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$user_id   = $_SESSION['user_id'] ?? null;
$authToken = $_COOKIE['auth_token'] ?? null;
$isAdmin   = (($_SESSION['status'] ?? '') === 'admin');

if (!$user_id || !$authToken) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND auth_token = ? LIMIT 1");
$stmt->bind_param("is", $user_id, $authToken);
$stmt->execute();
$auth = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$auth) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    exit;
}

$len = static fn(string $s): int => mb_strlen($s, 'UTF-8');
$uploadsDirFS = __DIR__ . '/../../uploads/';

$recipe_id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$category    = trim($_POST['category'] ?? '');
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$instruction = trim($_POST['instruction'] ?? '');
$ingredientsRaw = trim($_POST["ingredients"]);
$ingredients = validateIngredients($ingredientsRaw);

if ($recipe_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Bad recipe id"]);
    exit;
}

$validCategories = ['breakfast','lunch','dinner','dessert','snack'];
if ($len($name) < 3 || $len($name) > 100 || !preg_match(NAME_PATTERN, $name)) {
    echo json_encode(["status" => "error", "message" => "Name must be 3–100 chars, only letters, spaces, and hyphens allowed"]);
    exit;
}

if ($len($description) < 10 || $len($description) > 130 || !preg_match(TEXT_PATTERN, $description)) {
    echo json_encode(["status" => "error", "message" => "Description contains invalid characters"]);
    exit;
}

if ($len($instruction) < 20 || $len($instruction) > 5000 || !preg_match(TEXT_PATTERN, $instruction)) {
    echo json_encode(["status" => "error", "message" => "Instruction contains invalid characters"]);
    exit;
}

if ($len($ingredients) < 1 || $len($ingredients) > 50) {
    echo json_encode(["status" => "error", "message" => "Ingredients length is invalid"]);
    exit;
}

if ($isAdmin) {
    $stmt = $conn->prepare("SELECT image_path FROM recipes WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $recipe_id);
} else {
    $stmt = $conn->prepare("SELECT image_path FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $recipe_id, $user_id);
}
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Recipe not found or not allowed"]);
    exit;
}

$currentImage = $recipe['image_path'] ?? null;


$newImagePath = $currentImage;
if (isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['recipeImage'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "Image too large (max 2MB)"]);
        exit;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg','image/png'];
    if (!in_array($mime, $allowed, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName = uniqid('recipe_', true) . '.' . $ext;
    $dstFS   = $uploadsDirFS . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dstFS)) {
        echo json_encode(["status" => "error", "message" => "Failed to save image"]);
        exit;
    }
    $newImagePath = $newName;
}


if ($isAdmin) {
    $stmt = $conn->prepare("
        UPDATE recipes
           SET name=?, description=?, ingredients=?, instruction=?, category=?, image_path=?
         WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $description, $ingredients, $instruction, $category, $newImagePath, $recipe_id);
} else {
    $stmt = $conn->prepare("
        UPDATE recipes
           SET name=?, description=?, ingredients=?, instruction=?, category=?, image_path=?
         WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssssii", $name, $description, $ingredients, $instruction, $category, $newImagePath, $recipe_id, $user_id);
}

if ($stmt->execute()) {
    $stmt->close();
    if ($newImagePath !== $currentImage && $currentImage) {
        $oldFS = $uploadsDirFS . $currentImage;
        if (is_file($oldFS)) {
            @unlink($oldFS);
        }
    }
    echo json_encode(["status" => "success", "message" => "Recipe updated successfully"]);
    exit;
}

$stmt->close();
if ($newImagePath !== $currentImage) {
    $tmpFS = $uploadsDirFS . $newImagePath;
    if (is_file($tmpFS)) {
        @unlink($tmpFS);
    }
}
http_response_code(500);
echo json_encode(["status" => "error", "message" => "Failed to update recipe"]);
exit;
