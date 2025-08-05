<?php
$pageTitle ='Profile';
// $extra_css = '<link rel="stylesheet" href="/profile/profile-styles.css">';
$extra_css = '
    <link rel="stylesheet" href="/profile/profile-styles.css">
    <link rel="stylesheet" href="/registration/formregister.css">
    <link rel="stylesheet" href="/profile/settings/settings-styles.css">
    <link rel="stylesheet" href="/profile/password_change/password_change-styles.css">
    <link rel="stylesheet" href="/profile/posts/post-style.css">
    <link rel="stylesheet" href="/profile/overview/mostLikedRecipe-styles.css">
    <link rel="stylesheet" href="/profile/favourites/favourites-styles.css">
';
session_start();
$section = $_GET['section'] ?? 'overview';
include "../includes/header.php";
?>
    <div class="profile-wrapper">
    <div class="profile-container">
        <aside class="profile-sidebar">
            <ul class="menu">
                <li><a href="?section=overview" class="<?= $section === 'overview' ? 'active' : '' ?>">Overview</a></li>
                <li><a href="?section=posts" class="<?= $section === 'posts' ? 'active' : '' ?>">My recipes</a></li>
                <li><a href="?section=favourites" class="<?= $section === 'favourites' ? 'active' : '' ?>">Favourites</a></li>
                <li><a href="?section=settings" class="<?= $section === 'settings' ? 'active' : '' ?>">Settings</a></li>
                <li><a href="?section=change_pass" class="<?= $section === 'change_pass' ? 'active' : '' ?>">Change password</a></li>
                <li><a href="../profile/logout.php">Logout</a></li>
            </ul>
        </aside>
        <main class="profile-main">
            <?php
            $section = $_GET['section'] ?? 'overview';
            include "../includes/sections.php";
            loadSection($section);
            ?>
        </main>
    </div>
    </div>

<?php
include '../includes/footer.php';
