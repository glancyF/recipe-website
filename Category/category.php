<?php
session_start();
$pageTitle = "Category";
$extra_css = '<link rel="stylesheet" href="/Category/category-styles.css">
<link rel="stylesheet" href="/pagination.css">';
include '../includes/header.php';
?>

<div id="categoryFilters" class="category-buttons">
    <button data-category="all" class="active">All</button>
    <button data-category="breakfast">Breakfast</button>
    <button data-category="lunch">Lunch</button>
    <button data-category="dinner">Dinner</button>
    <button data-category="dessert">Dessert</button>
    <button data-category="snack">Snack</button>
</div>

<div class="category-section">
    <div class="category-wrapper">
        <div id="categoryRecipeContainer" class="recipes-grid"></div>
        <div id="categoryPagination" class="pagination fixed-bottom"></div>
    </div>
</div>
<script>
    window.currentUserId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;
    window.isAdmin = <?= json_encode(($_SESSION['status'] ?? '') === 'admin') ?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
</script>

<script src="category.js" type="module"></script>
<?php
include '../includes/footer.php';
?>
