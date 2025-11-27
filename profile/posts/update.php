<?php
/**
 * Update recipe (owner or admin) with optional image replacement.
 *
 * This endpoint updates a recipe's metadata (name, description, ingredients,
 * instruction, category) and optionally replaces its image. It enforces
 * authentication, token validation, ownership (unless admin), and performs
 * strict field validation. On image upload, it saves the original, generates
 * resized variants, and (on success) removes the old original image file.
 *
 * Behavior:
 * - Method: POST only (405 otherwise).
 * - Auth: requires session `user_id` and valid `auth_token` pair in DB.
 * - Permissions: admin may edit any recipe; regular users only their own.
 * - Validates:
 *   - category ∈ {breakfast,lunch,dinner,dessert,snack}
 *   - name: 3–100 chars, pattern `/^[A-Za-z\s,]+$/`
 *   - description: 10–130 chars, limited punctuation pattern
 *   - instruction: 20–5000 chars, limited punctuation pattern
 *   - ingredients: length 1–300 after `validateIngredients()`
 * - Image (optional):
 *   - Types: image/jpeg, image/png
 *   - Max size: 5 MB
 *   - Resizes to: 448×252 (card), 544×408 (classic), 936×312 (banner)
 * - On success: returns JSON `{status:"success", message:"Recipe updated successfully"}`
 * - On failures: returns JSON error with appropriate HTTP code.
 *
 * Security notes:
 * - Uses prepared statements everywhere.
 * - Verifies user via session + cookie token.
 * - Restricts edit to owner unless `$_SESSION['status']==='admin'`.
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @uses validateIngredients(string $raw): string  From utils/IngredientsControl.php
 * @uses resizeCover(string $src, string $dst, int $w, int $h): void  From utils/ImageMaker.php
 *
 * @global mysqli $conn Database connection from ../../db.php
 */

declare(strict_types=1);
global $conn;
include __DIR__ . '/../../utils/IngredientsControl.php';
include __DIR__ . '/../../utils/ImageMaker.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db.php';
/**
 * Enforce POST method.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}
/**
 * Authentication & role.
 *
 * @var int|null  $user_id  Current user ID from session.
 * @var string|null $authToken Cookie auth token.
 * @var bool $isAdmin Whether current user is admin.
 */
$user_id   = $_SESSION['user_id'] ?? null;
$authToken = $_COOKIE['auth_token'] ?? null;
$isAdmin   = (($_SESSION['status'] ?? '') === 'admin');

if (!$user_id || !$authToken) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}
/**
 * Verify session user + token pair.
 */
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

/**
 * Helper to get string length in UTF-8.
 *
 * @param string $s
 * @return int
 */
$len = static fn(string $s): int => mb_strlen($s, 'UTF-8');
/** @var string $uploadsDirFS Absolute path to uploads directory (originals). */
$uploadsDirFS = __DIR__ . '/../../uploads/';
/**
 * Collect & normalize inputs.
 *
 * @var int    $recipe_id
 * @var string $category
 * @var string $name
 * @var string $description
 * @var string $instruction
 * @var string $ingredientsRaw
 * @var string $ingredients Normalized via validateIngredients()
 */
$recipe_id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$category    = trim($_POST['category'] ?? '');
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$instruction = trim($_POST['instruction'] ?? '');
$ingredientsRaw = trim($_POST["ingredients"]);
$ingredients = validateIngredients($ingredientsRaw);
/** @var string $textAllowedPattern Allowed chars for description/instruction */
$textAllowedPattern = '/^[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+(,[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+)*$/';
/** @var string $NamePattern Allowed chars for name field */
$NamePattern = '/^[A-Za-z\s,]+$/';
/* -------------------- Basic validations -------------------- */
if ($recipe_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Bad recipe id"]);
    exit;
}
/** @var array<string> $validCategories */
$validCategories = ['breakfast','lunch','dinner','dessert','snack'];
if (!in_array($category, $validCategories, true)) {
    echo json_encode(["status" => "error", "message" => "Invalid category"]);
    exit;
}
if ($len($name) < 3 || $len($name) > 100 || !preg_match($NamePattern, $name)) {
    echo json_encode(["status" => "error", "message" => "Name must be 3–100 chars"]);
    exit;
}
if ($len($description) < 10 || $len($description) > 130 || !preg_match($textAllowedPattern, $description)) {
    echo json_encode(["status" => "error", "message" => "Description must be 10–300 chars"]);
    exit;
}
if ($len($instruction) < 20 || $len($instruction) > 5000 || !preg_match($textAllowedPattern, $instruction)) {
    echo json_encode(["status" => "error", "message" => "Instruction must be 20–5000 chars"]);
    exit;
}
if ($len($ingredients) < 1 || $len($ingredients) > 300) {
    echo json_encode(["status" => "error", "message" => "Ingredients length is invalid"]);
    exit;
}

/**
 * Fetch current recipe (admin: any; user: own only) to get current image.
 *
 * @var array{image_path:string}|null $recipe
 */
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
/** @var string|null $currentImage Current stored image filename (no path). */
$currentImage = $recipe['image_path'] ?? null;

/**
 * Handle optional image upload.
 *
 * @var string $newImagePath Filename to persist in DB (initially current image).
 */
$newImagePath = $currentImage;
if (isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    /** @var array{error:int,size:int,tmp_name:string,name:string} $file */
    $file = $_FILES['recipeImage'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "Image too large (max 5MB)"]);
        exit;
    }
    // MIME sniffing
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    /** @var array<string> $allowed Allowed mime types */
    $allowed = ['image/jpeg','image/png'];
    if (!in_array($mime, $allowed, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }

    /** @var string $ext Destination extension by mime */
    $ext = ($mime === 'image/png') ? 'png' : 'jpg';
    /** @var string $newName New unique file name */
    $newName = uniqid('recipe_', true) . '.' . $ext;
    /** @var string $dstFS Absolute path of saved original image */
    $dstFS   = $uploadsDirFS . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dstFS)) {
        echo json_encode(["status" => "error", "message" => "Failed to save image"]);
        exit;
    }
    $newImagePath = $newName;

    // Directories for resized variants
    /** @var string $cardDirFS */
    $cardDirFS    = __DIR__ . '/../../uploads/card/';
    /** @var string $classicDirFS */
    $classicDirFS = __DIR__ . '/../../uploads/classic/';
    /** @var string $bannerDirFS */
    $bannerDirFS  = __DIR__ . '/../../uploads/banner/';


    foreach ([$cardDirFS, $classicDirFS, $bannerDirFS] as $dir) {
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    }
    // Produce resized variants
    resizeCover($dstFS, $cardDirFS    . $newName,    448, 252);
    resizeCover($dstFS, $classicDirFS . $newName,    544, 408);
    resizeCover($dstFS, $bannerDirFS  . $newName,    936, 312);

}

/**
 * Persist updates (admin: any; user: own).
 *
 * @var mysqli_stmt $stmt
 */
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
/**
 * Handle success & cleanup of old original image file.
 *
 * Note: Thumbnails of the old image are not removed here on success (only the original),
 * but are removed in the failure fallback below if we attempted to replace the image.
 */
if ($stmt->execute()) {
    $stmt->close();
    if ($newImagePath !== $currentImage && $currentImage) {
        /** @var string $oldFS Absolute path to old original file */
        $oldFS = $uploadsDirFS . $currentImage;
        if (is_file($oldFS)) {
            @unlink($oldFS);
        }
    }
    echo json_encode(["status" => "success", "message" => "Recipe updated successfully"]);
    exit;
}

$stmt->close();
/**
 * Failure: if we replaced image name but update failed,
 * clean up both original and resized variants of the OLD image.
 */
if ($newImagePath !== $currentImage && $currentImage) {
    $oldBase = basename($currentImage);
    /** @var array<string> $pathsToDelete */
    $pathsToDelete = [
        $uploadsDirFS . $oldBase,
        __DIR__ . '/../../uploads/card/'    . $oldBase,
        __DIR__ . '/../../uploads/classic/' . $oldBase,
        __DIR__ . '/../../uploads/banner/'  . $oldBase,
    ];
    foreach ($pathsToDelete as $p) {
        if (is_file($p)) { @unlink($p); }
    }
}
http_response_code(500);
echo json_encode(["status" => "error", "message" => "Failed to update recipe"]);
exit;
