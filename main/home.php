<?php
global $conn;
header('Content-Type: application/json');
if(PHP_SESSION_NONE===session_status()){
    session_start();
}

require_once __DIR__ . '/../db.php';
$sql = "
  SELECT 
    r.id, r.user_id, r.name, r.description, r.category, r.image_path, r.created_at,
    u.username,
    COALESCE(rl.cnt, 0) AS like_count
  FROM recipes r
  JOIN users u ON u.id = r.user_id
  LEFT JOIN (
    SELECT recipe_id, COUNT(*) AS cnt 
    FROM recipe_likes 
    GROUP BY recipe_id
  ) rl ON rl.recipe_id = r.id
  ORDER BY COALESCE(rl.cnt, 0) DESC, r.id DESC
  LIMIT 1
";
$res = $conn->query($sql);
$recipe = $res ? $res->fetch_assoc() : null;
if(!$recipe){
    echo json_encode(['status'=>'empty']);
    exit;
}

$userId=$_SESSION['user_id'] ?? null;
$recipe['liked'] = false;
if($userId){
    $stmt = $conn->prepare("SELECT 1 FROM recipe_likes WHERE user_id = ? AND recipe_id = ? LIMIT 1");
    $stmt->bind_param('ii', $userId, $recipe['id']);
    $stmt->execute();
    $recipe['liked'] = $stmt->get_result()->num_rows > 0;
}
echo json_encode([
    'status' => 'success',
    'recipe' => $recipe
]);
