<?php
global $conn;
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access!');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['user_id'];
$query = "
SELECT r.*, COUNT(rl.id) AS like_count
FROM recipes r
LEFT JOIN recipe_likes rl ON r.id = rl.recipe_id
WHERE r.user_id = ?
GROUP BY r.id
ORDER BY like_count DESC
LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute([$userId]);
$result = $stmt->get_result();
$topRecipe = $result->fetch_assoc();
if($topRecipe){
    $stmtCheck = $conn->prepare("SELECT 1 FROM recipe_likes WHERE recipe_id = ? AND user_id = ?");
    $stmtCheck->bind_param("ii", $topRecipe["id"], $userId);
    $stmtCheck->execute();
    $liked = $stmtCheck->get_result()->num_rows >0;
    $topRecipe["liked"] = $liked;
}
else{
    $topRecipe=null;
}
