<?php
global $conn;
session_start();
require_once  __DIR__ . "/../../db.php";
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}
$user_id = $_SESSION['user_id'] ?? null;
$auth_token = $_COOKIE["auth_token"] ?? null;

if(!$user_id || !$auth_token) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND auth_token = ?");
$stmt->bind_param("is", $user_id, $auth_token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    exit;
}

$recipe_id = trim(htmlspecialchars($_POST["id"]) ?? null);
$category = trim(htmlspecialchars($_POST["category"]) ?? '');
$name = trim(htmlspecialchars($_POST["name"]) ?? '');
$description = trim(htmlspecialchars($_POST["description"]) ?? '');
$instruction = trim(htmlspecialchars($_POST["instruction"]) ?? '');
$ingredients = trim(htmlspecialchars($_POST["ingredients"]) ?? '');
if (!$recipe_id || !$category || !$name || !$description || !$instruction || !$ingredients) {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;

}
$stmt = $conn->prepare("SELECT image_path FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$recipe = $res->fetch_assoc();
$stmt->close();

if(!$recipe){
    echo json_encode(["status" => "error", "message" => "Recipe not found or not yours"]);
    exit;
}

$image_path = $recipe['image_path'];
if(isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png','image/jpg'];
    $file = $_FILES['recipeImage'];
    if(!in_array($file['type'], $allowed)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }
    if($file['size'] > 2*1024*1024) {
        echo json_encode(["status" => "error", "message" => "Image too large"]);
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('recipe_',true) . '.' . $ext;
    $destination = __DIR__ . "/../../uploads/" . $new_filename;
    if(move_uploaded_file($file['tmp_name'], $destination)) {
        if(!empty($image_path)) {
            $old_path = __DIR__ . "/../../uploads/" . $image_path;
            if(file_exists($old_path)) {
                unlink($old_path);
            }
        }
        $image_path = $new_filename;
    }
    else
    {
        echo json_encode(["status" => "error", "message" => "Failed uploading image"]);
        exit;
    }
}

$stmt = $conn->prepare("UPDATE recipes SET name = ?, description = ?, ingredients = ?, instruction = ?, category = ?, image_path = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssssssii", $name, $description, $ingredients, $instruction, $category, $image_path, $recipe_id, $user_id);
if($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Recipe updated successfully"]);
    exit;
}
else{
    echo json_encode(["status" => "error", "message" => "Failed to update recipe"]);
    exit;
}
$stmt->close();