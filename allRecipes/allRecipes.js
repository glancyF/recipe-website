import {LikeClicker} from "../utils/common.js";
import { escapeHtml, renderPagination, buildRecipeCardHTML } from "../utils/common.js";
import {bindDeleteHandler} from "../utils/common.js";

document.addEventListener('DOMContentLoaded',()=>{
   const container = document.getElementById('allRecipesContainer');
   const pagination = document.getElementById('allRecipesPagination');
   let currentPage = 1;


    async function fetchRecipes(page){
        const res = await fetch(`allRecipes/loadAllRecipes.php?page=${page}`,{credentials: 'include'});
        const data = await res.json();
        if(data.status !== 'success'){
            container.innerHTML='<p>Failed to load recipes. Try again.</p>';
            return;
        }
        renderRecipes(data.recipes);
        renderPagination(pagination,data.total,data.page,data.limit,fetchRecipes);
    }
    function renderRecipes(recipes){
        container.innerHTML='';
        if(recipes.length ===0){
            container.innerHTML='<p>No recipes found.</p>';
            return;
        }
        recipes.forEach(recipe => {
            const card = document.createElement('div');
            card.className='recipe-card';
            const isOwner = recipe.user_id === window.currentUserId;
            const isAdmin = Boolean(window.isAdmin);
            card.innerHTML = buildRecipeCardHTML(recipe, { isOwner, isAdmin, showTrophies: false });
            container.appendChild(card);
        });
        LikeClicker(container);
    }

    bindDeleteHandler(container, () => fetchRecipes(currentPage));


    fetchRecipes(currentPage);

});
