import {LikeClicker} from "../utils/common.js";
import { escapeHtml, renderPagination, buildRecipeCardHTML } from "../utils/common.js";
import {bindDeleteHandler} from "../utils/common.js";


document.addEventListener('DOMContentLoaded',()=>{
   const container = document.getElementById('categoryRecipeContainer');
   const pagination = document.getElementById('categoryPagination');
   const filterButtons = document.querySelectorAll('#categoryFilters button');

   let currentPage = 1;
   let selectedCategory = 'breakfast';

    filterButtons.forEach(btn => {
       btn.addEventListener('click',()=>{
           filterButtons.forEach(b => b.classList.remove('active'));
           btn.classList.add('active');
           selectedCategory = btn.dataset.category;
           currentPage=1;
           fetchRecipes(currentPage);
       });
    });

    async function fetchRecipes(page){
        const res = await fetch(`Category/loadCategory.php?page=${page}&category=${encodeURIComponent(selectedCategory)}`, {
            credentials: 'include'
        });
        const data = await res.json();
        if (data.status !== 'success') {
            container.innerHTML = "<p>Failed to load recipes</p>";
            return;
        }
        renderRecipes(data.recipes);
        renderPagination(pagination,data.total,data.page,data.limit,fetchRecipes)

    }

    function renderRecipes(recipes){
        container.innerHTML='';
        if(recipes.length ===0){
            container.innerHTML ='<p>No recipes found</p>'
        }
        recipes.forEach(recipe => {
            const card = document.createElement('div');
            card.className = 'recipe-card';
            const isOwner = recipe.user_id === window.currentUserId;
            const isAdmin = Boolean(window.isAdmin);
            card.innerHTML = buildRecipeCardHTML(recipe, { isOwner, isAdmin, showTrophies: false });
            container.appendChild(card);
        });
        LikeClicker(container);

    }
    bindDeleteHandler(container,()=> fetchRecipes(currentPage));
    fetchRecipes(currentPage);
});