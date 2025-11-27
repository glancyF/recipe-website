<?php
/**
 * Recipe creation endpoint.
 *
 * Accepts a POST request with authenticated user session/cookie, validates form fields,
 * processes an uploaded image, generates resized variants, and inserts the recipe
 * into the database. Responds with JSON.
 *
 *
 *
 * @license MIT
 * @author Valentyn Deshel
 * @uses validateIngredients(string $raw): string      Provided by utils/IngredientsControl.php
 * @uses resizeCover(string $src, string $dst, int $width, int $height): void  Provided by utils/ImageMaker.php
 *
 * @global mysqli $conn Database connection from ../db.php
 */
global $conn;
session_start();
require_once "../db.php";
include __DIR__ . '/../utils/IngredientsControl.php';
include __DIR__ . '/../utils/ImageMaker.php';
header("Content-Type: application/json");

/**
 * Directory for storing resized images used in different UI components.
 *
 * @var string $cardDir    Destination for card-sized images (e.g., 448x252)
 * @var string $classicDir Destination for classic-sized images (e.g., 544x408)
 * @var string $bannerDir  Destination for banner-sized images (e.g., 936x312)
 */
$cardDir   = '../uploads/card/';
$classicDir= '../uploads/classic/';
$bannerDir = '../uploads/banner/';

/**
 * Validate the recipe fields (name, description, instruction, category).
 *
 * Emits a JSON error and terminates the script (`exit`) on invalid input.
 *
 * @param string $name         Human-readable recipe name (3–100 chars, letters and spaces/commas).
 * @param string $description  Short description (10–130 chars, limited punctuation allowed).
 * @param string $instruction  Full cooking instructions (20–5000 chars, limited punctuation allowed).
 * @param string $category     One of: breakfast|lunch|dinner|dessert|snack.
 *
 * @return void
 */
function ControlRecipe($name,$description,$instruction,$category)
{
    /** @var string $textAllowedPattern Allowed characters for description/instruction. */
    $textAllowedPattern = '/^[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+(,[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+)*$/';
    if(strlen($name)<3 || strlen($name)>100 || !preg_match('/^[A-Za-z\s,]+$/', $name) ){
        echo json_encode(["status" => "error", "message" => "Invalid recipe name"]);
        exit;
    }
    if(strlen($description)<10 || strlen($description)>130 || !preg_match($textAllowedPattern, $description) ){
        echo json_encode(["status" => "error", "message" => "Invalid description"]);
        exit;
    }
    if(strlen($instruction)<20 || strlen($instruction)>5000 || !preg_match($textAllowedPattern, $instruction) ){
        echo json_encode(["status" => "error", "message" => "Invalid instruction"]);
        exit;
    }
    /** @var array<string> $validCategories Allowed category values. */
    $validCategories = ['breakfast', 'lunch', 'dinner', 'dessert', 'snack'];
    if(!in_array($category, $validCategories)){
        echo json_encode(["status" => "error", "message" => "Invalid category"]);
        exit;
    }
}
/**
 * Handle POST: authenticate user, validate payload, process image, persist to DB.
 *
 * Expected POST fields:
 * - name (string)
 * - description (string)
 * - ingredients (string; raw user text to be normalized by validateIngredients)
 * - instruction (string)
 * - category (string)
 * - recipeImage (file: JPEG/PNG up to 5MB)
 *
 * Session/Cookie:
 * - $_SESSION['user_id']
 * - $_COOKIE['auth_token']
 *
 * JSON Response:
 * - {"status":"success","message":"..."} on success
 * - {"status":"error","message":"..."} on failure
 */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /** @var int|string|null $user_id Authenticated user ID from session. */
    $user_id = $_SESSION['user_id'] ?? null;
    /** @var string|null $auth_token Authentication token from cookie. */
    $auth_token = $_COOKIE['auth_token'] ?? null;
    /** @var string $uploadDir Base directory for original uploads. */
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


    /** @var string $name */
    $name = trim($_POST["name"]);
    /** @var string $description */
    $description = trim($_POST["description"]);
    /** @var string $ingredientsRaw */
    $ingredientsRaw = trim($_POST["ingredients"]);
    /**
     * Normalized/validated ingredients string.
     * @var string $ingredients
     */
    $ingredients = validateIngredients($ingredientsRaw);
    /** @var string $instruction */
    $instruction = trim($_POST["instruction"]);
    /** @var string $category */
    $category = $_POST["category"] ?? '';

    if(!isset($_FILES['recipeImage']) || $_FILES['recipeImage']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
    /** @var array $image */
    $image = $_FILES['recipeImage'];
    /** @var int $maxSize Maximum image size in bytes (5MB). */
    $maxSize = 5 * 1024 * 1024;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $image['tmp_name']);
    finfo_close($finfo);

    /** @var array<string> $allowed Allowed MIME types. */
    $allowed = ['image/jpeg', 'image/png','image/jpg'];
    if (!in_array($mime, $allowed, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }
    if($image['size'] > $maxSize) {
        echo json_encode(["status" => "error", "message" => "Image is too big"]);
        exit;
    }
    /** @var string $imageExt Extension derived from original filename. */
    $imageExt = pathinfo($image['name'], PATHINFO_EXTENSION);
    /** @var string $imageName Unique stored filename (without path). */
    $imageName = uniqid('recipe_', true) . '.' . $imageExt;
    /** @var string $imagePath Absolute path (relative to script) for the original upload. */
    $imagePath = $uploadDir . $imageName;
    if(!move_uploaded_file($image['tmp_name'], $imagePath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
        exit;
    }
    ControlRecipe($name,$description,$instruction,$category);

    foreach ([$cardDir,$classicDir,$bannerDir] as $d) {
        if (!is_dir($d)) { mkdir($d, 0755, true); }
    }
    /** @var string $orig Path to original image. */
    $orig = $imagePath;
    resizeCover($orig, $cardDir . $imageName, 448, 252);
    resizeCover($orig, $classicDir . $imageName, 544, 408);
    resizeCover($orig, $bannerDir . $imageName, 936, 312);
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