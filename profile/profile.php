<?php
$pageTitle ='Profile';
// $extra_css = '<link rel="stylesheet" href="/profile/profile-styles.css">';
$extra_css = '
    <link rel="stylesheet" href="/profile/profile-styles.css">
    <link rel="stylesheet" href="/registration/formregister.css">
    <link rel="stylesheet" href="/profile/settings/settings-styles.css">
    <link rel="stylesheet" href="/profile/password_change/password_change-styles.css">
';
session_start();
include "../includes/header.php";
?>

    <div class="profile-container">
        <aside class="profile-sidebar">
            <ul class="menu">
                <li><a href="?section=overview">Overview</a></li>
                <li><a href="?section=posts">Favourites</a></li>
                <li><a href="?section=likes">Likes</a></li>
                <li><a href="?section=settings">Settings</a></li>
                <li><a href="?section=change_pass">Change password</a></li>
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

<?php
include '../includes/footer.php';
