<?php
/**
 * Profile section loader.
 *
 * Provides the `loadSection()` function used to dynamically include profile subpages
 * (e.g., overview, posts, settings, favourites, admin panel).
 * Ensures only allowed sections are included and enforces admin access restrictions.
 *
 * Behavior:
 * - Accepts a section name and includes the corresponding PHP file from `/profile/sections/`.
 * - If the section name is not allowed → defaults to `overview`.
 * - If the section is `admin` but the current user is not an admin → returns 403 Forbidden.
 * - If the section file does not exist → falls back to `overview.php`.
 *
 *
 *
 * @author  Valentyn Deshel
 *
 * @see isAdmin() Used to restrict access to admin-only sections.
 */
require_once __DIR__ . '/../includes/isAdmin.php';
/**
 * Load a user profile section securely.
 *
 * Dynamically includes the corresponding PHP file for the requested section,
 * while validating access rights and preventing arbitrary file inclusion.
 *
 * @param string $section The profile section identifier (e.g., "overview", "posts", "admin").
 *
 * @return void Outputs the included section content or an error message on failure.
 *
 * @throws void Terminates execution with HTTP 403 if non-admin attempts to access admin section.
 */
function loadSection(string $section): void {
    /** @var array<int,string> $allowed List of allowed section identifiers */
    $allowed = ['overview','posts','settings','change_pass','favourites','admin'];
    // --- Validate section name ---
    if (!in_array($section, $allowed, true)) {
        $section = 'overview';
    }
    // --- Restrict admin-only section ---
    if ($section === 'admin' && !isAdmin()) {
        http_response_code(403);
        echo 'Access denied';
        return;
    }

    // --- Define the inclusion flag to prevent direct access ---
    if (!defined('ALLOW_SECTION_INCLUDE')) {
        define('ALLOW_SECTION_INCLUDE', true);
    }
    /** @var string $base Directory where section files are stored */
    $base = __DIR__ . '/../profile/sections/';
    /** @var string $file Full path to the section file to be included */
    $file = $base . basename($section) . '.php';
    // --- Include the requested section or fallback ---
    if (file_exists($file)) {
        include $file;
    } else {
        include $base . 'overview.php';
    }
}