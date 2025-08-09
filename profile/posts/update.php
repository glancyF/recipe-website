<?php
// /profile/posts/update_recipe.php  (или твой путь)

declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection not established"]);
    exit;
}

/* ---------- Аутентификация по сессии + токену ---------- */
$user_id   = $_SESSION['user_id']      ?? null;
$authToken = $_COOKIE['auth_token']    ?? null;

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

/* ---------- Хелперы ---------- */
$len = static function (string $s): int { return mb_strlen($s, 'UTF-8'); };
$uploadsDirFS = __DIR__ . '/../../uploads/'; // абсолютный путь на диске

/* ---------- Данные формы ---------- */
$recipe_id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$category    = trim($_POST['category']    ?? '');
$name        = trim($_POST['name']        ?? '');
$description = trim($_POST['description'] ?? '');
$instruction = trim($_POST['instruction'] ?? '');
$ingredients = trim($_POST['ingredients'] ?? '');

if ($recipe_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Bad recipe id"]);
    exit;
}

/* ---------- Валидация ---------- */
$validCategories = ['breakfast','lunch','dinner','dessert','snack'];

if (!in_array($category, $validCategories, true)) {
    echo json_encode(["status" => "error", "message" => "Invalid category"]);
    exit;
}
if ($len($name) < 3 || $len($name) > 100) {
    echo json_encode(["status" => "error", "message" => "Name must be 3–100 chars"]);
    exit;
}
if ($len($description) < 10 || $len($description) > 300) {
    echo json_encode(["status" => "error", "message" => "Description must be 10–300 chars"]);
    exit;
}
if ($len($instruction) < 20 || $len($instruction) > 5000) {
    echo json_encode(["status" => "error", "message" => "Instruction must be 20–5000 chars"]);
    exit;
}
if ($len($ingredients) < 1 || $len($ingredients) > 300) {
    echo json_encode(["status" => "error", "message" => "Ingredients length is invalid"]);
    exit;
}

/* ---------- Проверяем, что рецепт принадлежит пользователю ---------- */
$stmt = $conn->prepare("SELECT image_path FROM recipes WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Recipe not found or not yours"]);
    exit;
}

$currentImage = $recipe['image_path'] ?? null;

/* ---------- Обработка нового изображения (если прислали) ---------- */
$newImagePath = $currentImage; // по умолчанию оставляем старую
if (isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['recipeImage'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "Image too large (max 2MB)"]);
        exit;
    }

    // Надёжная проверка MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg','image/png']; // jpg == jpeg
    if (!in_array($mime, $allowed, true)) {
        echo json_encode(["status" => "error", "message" => "Invalid image type"]);
        exit;
    }

    // Генерим новое имя и переносим
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName = uniqid('recipe_', true) . '.' . $ext;
    $dstFS   = $uploadsDirFS . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dstFS)) {
        echo json_encode(["status" => "error", "message" => "Failed to save image"]);
        exit;
    }

    // Только если UPDATE пройдёт — удалим старую. Пока просто помечаем.
    $newImagePath = $newName;
}

/* ---------- UPDATE (и только затем удаляем старый файл) ---------- */
$stmt = $conn->prepare("
    UPDATE recipes
       SET name = ?, description = ?, ingredients = ?, instruction = ?, category = ?, image_path = ?
     WHERE id = ? AND user_id = ?
");
$stmt->bind_param(
    "ssssssii",
    $name, $description, $ingredients, $instruction, $category, $newImagePath, $recipe_id, $user_id
);

if ($stmt->execute()) {
    $stmt->close();

    // если обновили путь и раньше была картинка — удаляем старую
    if ($newImagePath !== $currentImage && $currentImage) {
        $oldFS = $uploadsDirFS . $currentImage;
        if (is_file($oldFS)) {
            @unlink($oldFS);
        }
    }

    echo json_encode(["status" => "success", "message" => "Recipe updated successfully"]);
    exit;
}

// если UPDATE не прошёл, а новую картинку уже сохранили — убираем её, чтобы не плодить сироты
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
