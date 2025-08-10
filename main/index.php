<?php
session_start();
$pageTittle ='Home';
$extra_css = '<link rel="stylesheet" href="/main/main-style.css">';
include "../includes/header.php";
?>

<main class="home">
    <section id="featured" class="hero">
        <!-- сюда JS подставит карточку -->
        <div class="hero-skeleton">
            <div class="img-skeleton"></div>
            <div class="info-skeleton">
                <div class="line w60"></div>
                <div class="line w90"></div>
                <div class="line w40"></div>
            </div>
        </div>
    </section>

    <section class="more-info">
        <p>Want to see more? You’ll find even more recipes on these pages:</p>
    </section>

    <section class="home-cta">
        <a class="btn primary" href="/mostPopular/mostPopular.php">Most popular</a>
        <a class="btn" href="/allRecipes/allRecipe.php">All recipes</a>
        <a class="btn" href="/Category/category.php">Categories</a>
    </section>
</main>

<script>
    window.currentUserId = <?= (int)$_SESSION['user_id'] ?>;
    window.isAdmin = <?= json_encode(($_SESSION['status'] ?? '') === 'admin') ?>;
    window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
</script>
<script type="module" src="/main/home.js"></script>
<?php
include "../includes/footer.php";
?>
