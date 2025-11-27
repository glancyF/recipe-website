<?php
/**
 * Add Recipe handler (form POST).
 *
 * Validates user authentication and form fields, uploads and resizes the image,
 * persists the recipe into the database, and redirects with flash messages.
 *
 * Behavior:
 * - On non-POST requests redirects to /main/main.php
 * - On validation errors sets $_SESSION['flash_add_recipe'] and redirects back to /AddRecipe/addRecipe.php
 * - On success inserts a new recipe row and redirects to /main/main.php
 *
 * Dependencies:
 * - Requires a valid mysqli connection from ../db.php
 * - Uses resizeCover() from utils/ImageMaker.php to produce UI-ready image sizes
 *
 * Expected POST fields:
 * - name (string, 3–100 letters/spaces/commas)
 * - description (string, 10–130, limited punctuation)
 * - instruction (string, 20–5000, limited punctuation)
 * - category (string: breakfast|lunch|dinner|dessert|snack)
 * - ingredients (string; hidden field) OR ingredients_fallback (string; fallback textarea)
 * - recipeImage (file: JPEG/PNG/JPG up to 5MB)
 *
 * Session/Cookies:
 * - $_SESSION['user_id'] (int)
 * - $_COOKIE['auth_token'] (string)
 *
 * Redirect targets:
 * - /main/main.php (success or GET access)
 * - /AddRecipe/addRecipe.php (validation/DB/upload errors)
 *
 *
 * @author  Valentyn Deshel
 *
 *
 * @uses resizeCover(string $src, string $dst, int $width, int $height): void
 *
 * @global mysqli $conn Database connection
 */
session_start();
require_once "../db.php";
include __DIR__ . '/../utils/ImageMaker.php';
/**
 * Only allow POST requests; redirect otherwise.
 */
if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("Location:  /main/main.php"); exit;
}
/** @global mysqli $conn */
global $conn;
/**
 * Flash payload pieces.
 *
 * @var array<string,mixed> $old     Previously submitted form values (for repopulation)
 * @var array<string,string> $errors Field-specific and general validation errors
 * @var string|null $general         Optional general error message (not used directly here)
 */
$old    = [];
$errors = [];
$general= null;

/**
 * Authentication: session user id and cookie token.
 *
 * @var int|string|null $user_id
 * @var string|null $auth_token
 */
$user_id = $_SESSION["user_id"];
$auth_token = $_COOKIE["auth_token"];
if (!$user_id || !$auth_token) {
    $errors['general'] = "User not authenticated";
}
if (empty($errors)) {
    /** @var mysqli_stmt $stmt */
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND auth_token = ?");
    $stmt->bind_param("is", $user_id, $auth_token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) $errors['general'] = "Invalid authentication token";
    $stmt->close();
}
/**
 * Input fields (trimmed).
 *
 * @var string $name
 * @var string $description
 * @var string $instruction
 * @var string $category
 * @var string $hiddenIng
 * @var string $fallbackIng
 * @var string $ingredientsRaw
 */
$name        = trim($_POST["name"] ?? '');
$description = trim($_POST["description"] ?? '');
$instruction = trim($_POST["instruction"] ?? '');
$category    = $_POST["category"] ?? '';
$hiddenIng   = trim($_POST["ingredients"] ?? '');
$fallbackIng = trim($_POST["ingredients_fallback"] ?? '');
$ingredientsRaw = $hiddenIng !== '' ? $hiddenIng : $fallbackIng;
/**
 * Validation rules.
 *
 * @var string $textAllowedPattern Allowed characters for description/instruction
 * @var array<string> $validCategories Allowed recipe categories
 */
$textAllowedPattern = '/^[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+(,[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+)*$/';
$validCategories    = ['breakfast','lunch','dinner','dessert','snack'];
/* --- Field validations --- */
if ($name === '' || strlen($name) < 3 || strlen($name) > 100 || !preg_match('/^[A-Za-z\s,]+$/', $name)) {
    $errors['name'] = "Only letters and spaces are allowed";
}
if ($description === '' || strlen($description) < 10 || strlen($description) > 130 || !preg_match($textAllowedPattern, $description)) {
    $errors['description'] = "Invalid symbols or length";
}
if ($instruction === '' || strlen($instruction) < 20 || strlen($instruction) > 5000 || !preg_match($textAllowedPattern, $instruction)) {
    $errors['instruction'] = "Invalid symbols or length";
}
if (!in_array($category, $validCategories, true)) {
    $errors['category'] = "Select valid category";
}
if ($ingredientsRaw === '') {
    $errors['ingredients'] = "Please add at least one ingredient";
}
/**
 * Ingredients normalization and per-item validation.
 *
 * Split by semicolons/newlines; trim; collapse empties.
 *
 * @var array<int,string> $parts
 */
$parts = preg_split('/[;\r\n]+/', $ingredientsRaw);
$parts = array_values(array_filter(array_map('trim', $parts)));
if (count($parts) === 0) {
    $errors['ingredients'] = "Please add at least one ingredient";
}
if (count($parts) > 78) {
    $errors['ingredients'] =  "Too many ingredients (max 78)";

}
/** @var string $pattern Allowed characters for each ingredient item */
$pattern = '/^[A-Za-z0-9+\-,.%:;() ]+$/u';
foreach ($parts as $ing) {
    if (mb_strlen($ing, 'UTF-8') > 50) {
       $errors['ingredients']  =  "Each ingredient must be ≤ 50 characters";
    }
    if (!preg_match($pattern, $ing)) {
        $errors['ingredients'] =  "Ingredient contains invalid characters";
    }
}
/** @var string $ingredients Normalized ingredient string separated by semicolons */
$ingredients = implode(';', $parts);
/* --- File upload validations --- */
if (!isset($_FILES['recipeImage']) || $_FILES['recipeImage']['error'] !== UPLOAD_ERR_OK) {
    $errors['recipeImage'] = "Image is required";
} else {
    /** @var int $maxSize Maximum upload size (5MB) */
    $maxSize = 5 * 1024 * 1024;
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $_FILES['recipeImage']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg','image/png','image/jpg'], true)) {
        $errors['recipeImage'] = "Only JPG, PNG, JPEG allowed";
    } elseif (($_FILES['recipeImage']['size'] ?? 0) > $maxSize) {
        $errors['recipeImage'] = "Max 5MB";
    }
}
/**
 * Values to repopulate the form on error.
 *
 * @var array<string,string> $old
 */
$old = [
    'name' => $name,
    'description' => $description,
    'instruction' => $instruction,
    'category' => $category,
    'ingredients_fallback' => $fallbackIng !== '' ? $fallbackIng : $ingredientsRaw,
];
/* --- Redirect back on validation errors --- */
if (!empty($errors)) {
    $_SESSION['flash_add_recipe'] = [
        'old'    => $old,
        'errors' => $errors,
        'general'=> isset($errors['general']) ? $errors['general'] : "Please fix the errors below"
    ];
    header("Location:  /AddRecipe/addRecipe.php");
    exit;
}
/**
 * Upload destinations for original and resized images.
 *
 * @var string $uploadDir  Path for original uploads
 * @var string $cardDir    Path for card-size variant (448x252)
 * @var string $classicDir Path for classic-size variant (544x408)
 * @var string $bannerDir  Path for banner-size variant (936x312)
 */
$uploadDir  = '../uploads/';
$cardDir    = '../uploads/card/';
$classicDir = '../uploads/classic/';
$bannerDir  = '../uploads/banner/';
/**
 * Compute target file names for the upload.
 *
 * @var string $imageExt
 * @var string $imageName
 * @var string $imagePath
 */
$imageExt  = pathinfo($_FILES['recipeImage']['name'], PATHINFO_EXTENSION);
$imageName = uniqid('recipe_', true) . '.' . $imageExt;
$imagePath = $uploadDir . $imageName;
/* --- Move uploaded file --- */
if (!move_uploaded_file($_FILES['recipeImage']['tmp_name'], $imagePath)) {
    $_SESSION['flash_add_recipe'] = [
        'old'    => $old,
        'errors' => ['recipeImage' => 'Upload failed'],
        'general'=> 'Upload failed'
    ];
    header("Location: /AddRecipe/addRecipe.php");
    exit;
}
/* --- Ensure directories exist and generate resized variants --- */
foreach ([$cardDir,$classicDir,$bannerDir] as $d) { if (!is_dir($d)) { mkdir($d, 0755, true); } }
/**
 * Create UI-ready image sizes using ImageMaker::resizeCover().
 *
 * @see resizeCover()
 */
resizeCover($imagePath, $cardDir . $imageName,    448, 252);
resizeCover($imagePath, $classicDir . $imageName, 544, 408);
resizeCover($imagePath, $bannerDir  . $imageName, 936, 312);
/* --- Insert recipe row --- */
$stmt = $conn->prepare("INSERT INTO recipes
    (user_id, name, description, ingredients, instruction, category, image_path, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param('issssss', $user_id, $name, $description, $ingredients, $instruction, $category, $imageName);

if ($stmt->execute()) {
    header("Location: /main/main.php");
    exit;
} else {
    /* --- DB error fallback --- */
    $_SESSION['flash_add_recipe'] = [
        'old'    => $old,
        'errors' => ['general' => 'Database error'],
        'general'=> 'Database error: '.$stmt->error
    ];
    header("Location:  /AddRecipe/addRecipe.php");
    exit;
}