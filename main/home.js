import { LikeClicker } from "../likes/likeClicker.js";
import { bindDeleteHandler } from "../utils/deleHandler.js";
import { escapeHtml } from "../utils/recipeHelper.js";

document.addEventListener("DOMContentLoaded", () => {
    const featured = document.getElementById("featured");

    const loadFeatured = async () => {
        try {
            const res = await fetch("/main/home.php", { credentials: "include" });
            const data = await res.json();

            if (data.status !== "success" || !data.recipe) {
                featured.innerHTML =
                    `<div class="empty">No recipes yet. ` +
                    `<a href="/AddRecipe/addRecipe.php">Add your first recipe</a></div>`;
                return;
            }

            const r = data.recipe;

            const currentUserId = Number(window.currentUserId || 0);
            const isOwner = Number(r.user_id) === currentUserId;
            const isAdmin = Boolean(window.isAdmin);

            featured.innerHTML = `
  <div class="hero-card">
    <div class="hero-image">
      <img src="/uploads/${escapeHtml(r.image_path)}" alt="Recipe image">
      <div class="like-container" data-id="${r.id}">
        <i class="fa fa-heart${r.liked ? " liked" : ""}"></i>
        <span class="like-count">${r.like_count}</span>
      </div>
    </div>

    <div class="hero-info">
      <h1>${escapeHtml(r.name)}</h1>
      <p class="meta"><i class="fa fa-user"></i> ${escapeHtml(r.username)}</p>
      <p class="desc">${escapeHtml(r.description)}</p>

      <span class="category">${escapeHtml(r.category)}</span>
      <p class="date">${new Date(r.created_at).toLocaleDateString()}</p>

      <div class="bottom-actions">
        <div class="view">
          <a href="/recipes/recipes.php?id=${r.id}">View</a>
        </div>
        ${
                (isOwner || isAdmin)
                    ? `<div class="card-header">
                 <a href="/profile/posts/edit.php?id=${r.id}" title="Edit">
                   <i class="fas fa-edit"></i>
                 </a>
                 <i class="fas fa-trash delete-icon" data-id="${r.id}" title="Delete"></i>
               </div>`
                    : ''
            }
      </div>
    </div>
  </div>
`;


            // лайки по месту карточки
            LikeClicker(featured);

            // удаление с авто-обновлением «избранного» рецепта
            bindDeleteHandler(featured, loadFeatured);
        } catch (e) {
            featured.innerHTML = `<div class="error">Failed to load. Please try again later.</div>`;
        }
    };

    loadFeatured();
});
