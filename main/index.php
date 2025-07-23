<?php
$pageTittle ='Home';
$extra_css = '<link rel="stylesheet" href="/main/main-style.css">';
include "../includes/header.php";
?>

<h1>Main</h1>


<div class="recipes-main">
    <img src="../images/knedlik%20site.jpg" alt="image of dish here" class="recipes-main-image">
    <div class="recipe-main-content">
        <h2>Name of a dish</h2>
        <p>Describe</p>
        <a href="../recipes/recipes.php" class="recipes-main-button">More</a>
    </div>

</div>

<?php
include "../includes/footer.php";
?>


