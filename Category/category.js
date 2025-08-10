import {LikeClicker} from "../likes/likeClicker.js";
import { escapeHtml, renderPagination } from "../utils/recipeHelper.js";
import {bindDeleteHandler} from "../utils/deleHandler.js";


document.addEventListener('DOMContentLoaded',()=>{
   const container = document.getElementById('categoryRecipeContainer');
   const pagination = document.getElementById('categoryPagination');
   const filterButtons = document.querySelectorAll('#categoryFilters button');

   let currentPage = 1;
   let selectedCategory = 'all';

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
        const res = await fetch(`/Category/loadCategory.php?page=${page}&category=${encodeURIComponent(selectedCategory)}`, {
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
            card.innerHTML = `
                <div class="meta">
                    <span><i class="fa fa-user"></i> ${escapeHtml(recipe.username)}</span>
                </div>
                <div class="image-container">
                    <img src="/uploads/${recipe.image_path}" alt="Recipe image">
                    <div class="like-container" data-id="${recipe.id}">
                        <i class="fa fa-heart${recipe.liked ? ' liked' : ''}"></i>
                        <span class="like-count">${recipe.like_count}</span>
                    </div>
                </div>
                <h3>${escapeHtml(recipe.name)}</h3>
                <p>${escapeHtml(recipe.description)}</p>
                <span class="category">${escapeHtml(recipe.category)}</span>
                <p class="date">${new Date(recipe.created_at).toLocaleDateString()}</p>
                <div class="bottom-actions">
                    ${(isOwner || isAdmin) ? `
                        <div class="card-header">
                            <a href="/profile/posts/edit.php?id=${recipe.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <i class="fas fa-trash delete-icon" data-id="${recipe.id}" title="Delete"></i>
                        </div>` : `<div></div>`}
                    <div class="view">
                        <a href="/recipes/recipes.php?id=${recipe.id}">View</a>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
        LikeClicker(container);

    }
    bindDeleteHandler(container,()=> fetchRecipes(currentPage));
    fetchRecipes(currentPage);
});