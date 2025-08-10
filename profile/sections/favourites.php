<section class="content-area">
    <h2>Favourites</h2>
    <div id="likedRecipes" class="recipes-grid"></div>
    <div id="likedPagination" class="pagination-controls fixed-bottom"></div>
</section>
<script>
    window.currentUserId = <?= (int)$_SESSION['user_id'] ?>;
    window.isAdmin = <?= json_encode(($_SESSION['status'] ?? '') === 'admin') ?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
</script>
<script src="/profile/favourites/favourites.js" type="module"></script>

<script type="module" src="/profile/admin/min-admin.js"></script>