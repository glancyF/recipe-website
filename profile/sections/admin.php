<?php
if (!defined('ALLOW_SECTION_INCLUDE')) { http_response_code(403); exit('Forbidden'); }
require_once __DIR__ . '/../../includes/isAdmin.php';
if (!isAdmin()) { http_response_code(403); exit('Access denied'); }
require_once __DIR__ . '/../../db.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<section class="admin-users">
    <h2>User statuses</h2>

    <table class="users-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username / Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody id="usersTbody">
        <tr><td colspan="4">Loading…</td></tr>
        </tbody>
    </table>

    <div id="usersPagination" class="pagination-controls fixed-bottom"></div>
</section>

<script>
    window.currentUserId = <?= (int)($_SESSION['user_id'])?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token']) ?>;
</script>

<script type="module" src="/profile/admin/min-admin.js"></script>