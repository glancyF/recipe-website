<?php
require_once __DIR__ . '/../includes/isAdmin.php';

function loadSection(string $section): void {
    $allowed = ['overview','posts','settings','change_pass','favourites','admin'];

    if (!in_array($section, $allowed, true)) {
        $section = 'overview';
    }

    if ($section === 'admin' && !isAdmin()) {
        http_response_code(403);
        echo 'Access denied';
        return;
    }

    // запрещаем прямой include этих файлов мимо profile.php
    if (!defined('ALLOW_SECTION_INCLUDE')) {
        define('ALLOW_SECTION_INCLUDE', true);
    }
    $base = __DIR__ . '/../profile/sections/';
    $file = $base . basename($section) . '.php';

    if (file_exists($file)) {
        include $file;
    } else {
        include $base . 'overview.php';
    }
}