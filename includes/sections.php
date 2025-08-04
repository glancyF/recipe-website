<?php

function loadSection($section)
{
    $allowed_sections = ['overview', 'posts', 'settings','change_pass'];
    $base_path = __DIR__ . '/../profile/sections/';
    $filename = $base_path . basename($section) . '.php';

    if (in_array($section, $allowed_sections) && file_exists($filename)) {
        include $filename;
    } else {
        include $base_path . 'overview.php';
    }
}
