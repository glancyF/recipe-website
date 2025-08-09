<?php
global $conn;
require_once __DIR__ . '/../../includes/isAdmin.php';
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isAdmin()) { http_response_code(403); echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

$page  = isset($_GET['page'])  ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = max(5, min($limit, 50));
$offset = ($page - 1) * $limit;

$totalRes = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];
$total = (int)$totalRow['total'];


$stmt = $conn->prepare("
    SELECT id, username, email, status
    FROM users
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
$users =$res ? $res->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode([
    'status' => 'success',
    'users'  => $users,
    'total'  => $total,
    'page'   => $page,
    'limit'  => $limit
]);