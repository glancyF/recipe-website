<?php
if (!defined('ALLOW_SECTION_INCLUDE')) { http_response_code(403); exit('Forbidden'); }
require_once __DIR__ . '/../../includes/isAdmin.php';
if (!isAdmin()) { http_response_code(403); exit('Access denied'); }
?>
<h1>HELLO ADMIN</h1>
