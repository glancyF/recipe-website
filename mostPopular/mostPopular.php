<?php
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

<script src="/mostPopular/mostPopular.js" type="module"></script>

<?php
include '../includes/footer.php';
?>