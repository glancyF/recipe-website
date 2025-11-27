<?php
/**
 * Admin API endpoint: Fetch paginated list of users.
 *
 * This endpoint is restricted to admin users only.
 * It returns a paginated list of users from the database, including
 * basic information such as ID, username, email, and status.
 *
 * Behavior:
 * - Requires administrator privileges (403 Forbidden if not admin).
 * - Supports pagination through `?page` and `?limit` query parameters.
 * - Returns paginated list of users in JSON format with total count.
 *
 * Query parameters:
 * - `page`  (int, optional) — current page number (default: 1, min: 1)
 * - `limit` (int, optional) — number of users per page (default: 10, min: 5, max: 50)
 *
 * Example JSON response:
 * {
 *   "status": "success",
 *   "users": [
 *     {
 *       "id": 1,
 *       "username": "admin",
 *       "email": "admin@example.com",
 *       "status": "admin"
 *     },
 *     {
 *       "id": 2,
 *       "username": "user123",
 *       "email": "user123@example.com",
 *       "status": "user"
 *     }
 *   ],
 *   "total": 37,
 *   "page": 1,
 *   "limit": 10
 * }
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @global mysqli $conn Database connection from ../../db.php
 */
global $conn;
require_once __DIR__ . '/../../includes/isAdmin.php';
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');
/**
 * Ensure session is started.
 */
if(session_status() === PHP_SESSION_NONE) session_start();
/**
 * Restrict access to administrators only.
 */
if (!isAdmin()) { http_response_code(403); echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }
/**
 * Pagination setup.
 *
 * @var int $page   Current page number (>=1)
 * @var int $limit  Results per page (5–50)
 * @var int $offset SQL offset
 */
$page  = isset($_GET['page'])  ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = max(5, min($limit, 50));
$offset = ($page - 1) * $limit;
/**
 * Retrieve total user count.
 *
 * @var mysqli_result|false $totalRes
 * @var array{total:int}|null $totalRow
 * @var int $total
 */
$totalRes = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];
$total = (int)$totalRow['total'];

/**
 * Fetch paginated user records.
 *
 * Each record includes:
 * - id
 * - username
 * - email
 * - status
 *
 * @var mysqli_stmt $stmt
 * @var mysqli_result|false $res
 * @var array<int,array<string,mixed>> $users
 */
$stmt = $conn->prepare("
    SELECT id, username, email, status
    FROM users
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
$users =$res ? $res->fetch_all(MYSQLI_ASSOC) : [];
/**
 * JSON response payload.
 */
echo json_encode([
    'status' => 'success',
    'users'  => $users,
    'total'  => $total,
    'page'   => $page,
    'limit'  => $limit
]);