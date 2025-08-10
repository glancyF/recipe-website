<?php
session_start();
$pageTitle ='All recipes';
$extra_css = '<link rel="stylesheet" href="/allRecipes/allRecipes-styles.css">
<link rel="stylesheet" href="/pagination.css">
';
include "../includes/header.php";
?>


<div class="all-recipes-section">
    <h1>All Recipes</h1>
    <div class="all-recipes-wrapper">
        <div id="allRecipesContainer" class="recipes-grid"></div>
        <div id="allRecipesPagination" class="pagination fixed-bottom"></div>
    </div>
</div>
<script>
    window.currentUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;
    window.isAdmin = <?= json_encode(($_SESSION['status'] ?? '') === 'admin') ?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
</script>
<script src="/allRecipes/allRecipes.js" type="module"></script>


<?php
include "../includes/footer.php";
?>
