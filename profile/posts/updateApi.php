<?php
/**
 * Handle recipe edit form submission (owner or admin).
 *
 * Processes the HTML form for updating a recipe. Validates authentication
 * (session + auth token), checks ownership (unless admin), validates fields,
 * optionally processes a new image (with resize variants), updates the recipe,
 * and redirects back either to the profile page (on success) or back to the
 * edit screen with flash errors (on failure).
 *
 * Behavior:
 * - Method: POST only; otherwise redirect back to edit page.
 * - Auth: requires session user and valid auth_token in DB.
 * - Permission: admin can edit any recipe; regular user only own recipes.
 * - Validation:
 *   - name: letters/spaces/commas, 3–100 chars
 *   - description: limited punctuation, 10–130 chars
 *   - instruction: limited punctuation, 20–5000 chars
 *   - category ∈ {breakfast,lunch,dinner,dessert,snack}
 *   - ingredients: 1..86 items; each ≤ 50 chars; allowed chars only
 * - Image (optional): JPG/PNG up to 5MB; generates 448×252, 544×408, 936×312
 * - On validation/auth errors: sets `$_SESSION['flash_edit_recipe']` and redirects back
 * - On success: redirects to `/profile/profile.php`
 *
 * Flash example (error case):
 * ```php
 * $_SESSION['flash_edit_recipe'] = [
 *   'old'    => [...],
 *   'errors' => ['name' => 'Only letters and spaces are allowed (3–100)'],
 *   'general'=> 'Please fix the errors below'
 * ];
 * ```
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/isAdmin.php';
/** @var array<string,string> $errors */
$errors = [];
/** @var int $recipeId Target recipe ID from POST */
$recipeId = (int)($_POST['id'] ?? 0);
/** @var string $backUrl Redirect target on failure */
$backUrl  = "/profile/posts/edit.php?id={$recipeId}";
/**
 * Guard: method and recipe id.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $recipeId <= 0) {
    header("Location: {$backUrl}"); exit;
}

/**
 * Authentication (session + cookie token presence).
 *
 * @var int|null    $user_id
 * @var string|null $auth_token
 */
$user_id    = $_SESSION['user_id'] ?? null;
$auth_token = $_COOKIE['auth_token'] ?? null;
if (!$user_id || !$auth_token) $errors['general'] = 'User not authenticated';

/**
 * Validate token pair against DB if no prior auth errors.
 */
if (!$errors) {
    /** @var mysqli_stmt $stmt */
    $stmt = $conn->prepare("SELECT id FROM users WHERE id=? AND auth_token=? LIMIT 1");
    $stmt->bind_param("is", $user_id, $auth_token);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows === 0) $errors['general'] = 'Invalid authentication token';
    $stmt->close();
}

/** @var bool $isAdmin Current user is admin? */
$isAdmin = (($_SESSION['status'] ?? '') === 'admin');
/**
 * Fetch recipe ownership and current image path; scope by role.
 *
 * @var array{user_id:int,image_path:string}|null $rec
 */
if (!$errors) {
    if ($isAdmin) {
        $stmt = $conn->prepare("SELECT user_id,image_path FROM recipes WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $recipeId);
    } else {
        $stmt = $conn->prepare("SELECT user_id,image_path FROM recipes WHERE id=? AND user_id=? LIMIT 1");
        $stmt->bind_param("ii", $recipeId, $user_id);
    }
    $stmt->execute();
    $rec = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$rec) $errors['general'] = 'Recipe not found or access denied';
}
/**
 * Collect fields.
 *
 * @var string $name
 * @var string $description
 * @var string $instruction
 * @var string $category
 * @var string $hiddenIng
 * @var string $fallbackIng
 * @var string $ingredientsRaw
 * @var string $ingredients Normalized later
 */
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$instruction = trim($_POST['instruction'] ?? '');
$category    = $_POST['category'] ?? '';

$hiddenIng   = trim($_POST['ingredients'] ?? '');
$fallbackIng = trim($_POST['ingredients_fallback'] ?? '');

$ingredientsRaw = $fallbackIng !== '' ? $fallbackIng : $hiddenIng;
/**
 * Validate text fields.
 */
$textAllowedPattern = '/^[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+(,[A-Za-z0-9+\-,.%:;()\'"*!\/ \r\n]+)*$/';
if ($name === '' || mb_strlen($name) < 3 || mb_strlen($name) > 100 || !preg_match('/^[A-Za-z\s,]+$/', $name)) {
    $errors['name'] = 'Only letters and spaces are allowed (3–100)';
}
if ($description === '' || mb_strlen($description) < 10 || mb_strlen($description) > 130 || !preg_match($textAllowedPattern, $description)) {
    $errors['description'] = 'Invalid description';
}
if ($instruction === '' || mb_strlen($instruction) < 20 || mb_strlen($instruction) > 5000 || !preg_match($textAllowedPattern, $instruction)) {
    $errors['instruction'] = 'Invalid instruction';
}
/** @var array<string> $validCategories */
$validCategories = ['breakfast','lunch','dinner','dessert','snack'];
if (!in_array($category, $validCategories, true)) {
    $errors['category'] = 'Select valid category';
}
/**
 * Validate ingredients list: presence, size, characters; build normalized string.
 *
 * @var string $ingredients
 */
if ($ingredientsRaw === '') {
    $errors['ingredients'] = 'Please add at least one ingredient';
} else {
    $parts = preg_split('/[;\r\n]+/', $ingredientsRaw);
    $parts = array_values(array_filter(array_map('trim', $parts)));
    if (count($parts) === 0) $errors['ingredients'] = 'Please add at least one ingredient';
    if (count($parts) > 86) $errors['ingredients'] = 'Too many ingredients (max 86)';
    $pat = '/^[A-Za-z0-9+\-,.%:;() ]+$/u';
    foreach ($parts as $ing) {
        if (mb_strlen($ing, 'UTF-8') > 50) { $errors['ingredients'] = 'Each ingredient ≤ 50 chars'; break; }
        if (!preg_match($pat, $ing))       { $errors['ingredients'] = 'Ingredient has invalid chars'; break; }
    }
    $ingredients = implode(';', $parts);
}
/**
 * Preserve submitted values for re-render on error.
 *
 * @var array<string,string> $old
 */

$old = [
    'name'                 => $name,
    'description'          => $description,
    'instruction'          => $instruction,
    'category'             => $category,
    'ingredients_fallback' => $ingredientsRaw,
];
/**
 * If there are errors, set flash and redirect back.
 */
if (!empty($errors)) {
    $_SESSION['flash_edit_recipe'] = [
        'old'    => $old,
        'errors' => $errors,
        'general'=> $errors['general'] ?? 'Please fix the errors below'
    ];
    header("Location: {$backUrl}"); exit;
}

/**
 * Optional image upload & resize pipeline.
 *
 * @var string|null $imageName Final image filename to store (existing or new)
 */
$imageName = $rec['image_path'] ?? null;
if (isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['recipeImage']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_edit_recipe'] = ['old'=>$old,'errors'=>['recipeImage'=>'Image upload failed'],'general'=>'Image upload failed'];
        header("Location: {$backUrl}"); exit;
    }
    $max = 5 * 1024 * 1024;
    if ($_FILES['recipeImage']['size'] > $max) {
        $_SESSION['flash_edit_recipe'] = ['old'=>$old,'errors'=>['recipeImage'=>'Max 5MB'],'general'=>'Max 5MB'];
        header("Location: {$backUrl}"); exit;
    }
    $f = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($f, $_FILES['recipeImage']['tmp_name']); finfo_close($f);
    if (!in_array($mime, ['image/jpeg','image/png','image/jpg'], true)) {
        $_SESSION['flash_edit_recipe'] = ['old'=>$old,'errors'=>['recipeImage'=>'Only JPG/PNG allowed'],'general'=>'Only JPG/PNG allowed'];
        header("Location: {$backUrl}"); exit;
    }
    /** @var string $uploadsDirFS Base uploads dir */
    $uploadsDirFS = __DIR__ . '/../../uploads/';
    foreach ([$uploadsDirFS, $uploadsDirFS.'card/', $uploadsDirFS.'classic/', $uploadsDirFS.'banner/'] as $d) {
        if (!is_dir($d)) @mkdir($d, 0755, true);
    }
    /** @var string $ext Destination extension */
    $ext = ($mime === 'image/png') ? 'png' : 'jpg';
    /** @var string $new New unique file name */
    $new = uniqid('recipe_', true) . '.' . $ext;
    /** @var string $dst Absolute path to original image */
    $dst = $uploadsDirFS . $new;
    if (move_uploaded_file($_FILES['recipeImage']['tmp_name'], $dst)) {
        require_once __DIR__ . '/../../utils/ImageMaker.php';
        resizeCover($dst, $uploadsDirFS.'card/'.$new,    448, 252);
        resizeCover($dst, $uploadsDirFS.'classic/'.$new, 544, 408);
        resizeCover($dst, $uploadsDirFS.'banner/'.$new,  936, 312);
        $imageName = $new;
    }
}

/**
 * Persist updates with role-aware constraints.
 */
if ($isAdmin) {
    $stmt = $conn->prepare("UPDATE recipes SET name=?, description=?, ingredients=?, instruction=?, category=?, image_path=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $description, $ingredients, $instruction, $category, $imageName, $recipeId);
} else {
    $stmt = $conn->prepare("UPDATE recipes SET name=?, description=?, ingredients=?, instruction=?, category=?, image_path=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssssii", $name, $description, $ingredients, $instruction, $category, $imageName, $recipeId, $user_id);
}
$ok = $stmt->execute();
$stmt->close();
/**
 * Final redirect: success → profile, failure → back to edit.
 */
header("Location: " . ($ok ? "/profile/profile.php" : $backUrl));
exit;
