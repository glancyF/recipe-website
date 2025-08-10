<?php
session_start();
$pageTitle = "Most popular Recipes";
$extra_css='<link rel="stylesheet" type="text/css" href="/mostPopular/mostPopular-styles.css">';
include '../includes/header.php';
?>

    <div class="popular-section">
        <h1>Most Popular Recipes</h1>
        <div class="popular-wrapper">
            <div id="popularRecipesContainer" class="recipes-grid"></div>
            <div id="popularPagination" class="pagination fixed-bottom"></div>
        </div>
    </div>
    <script>
        window.currentUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;
        window.isAdmin = <?= json_encode(($_SESSION['status'] ?? '') === 'admin') ?>;
        window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
    </script>
<script src="/mostPopular/mostPopular.js" type="module"></script>

<?php
include '../includes/footer.php';
?>