import { LikeClicker } from "../../likes/likeClicker.js";
import { escapeHtml, renderPagination } from "../../utils/recipeHelper.js";
import { bindDeleteHandler } from "../../utils/deleHandler.js";

document.addEventListener("DOMContentLoaded", () => {
    const container  = document.getElementById("likedRecipes");
    const pager      = document.getElementById("likedPagination");

    let currentPage = 1;

    async function fetchLiked(page = 1) {
        try {
            const res  = await fetch(`/profile/favourites/loadFavourites.php?page=${page}`, { credentials: "include" });
            const data = await res.json();

            if (data.status !== "success") {
                container.innerHTML = "<p>Failed to load liked recipes.</p>";
                pager.innerHTML = "";
                return;
            }

            renderRecipes(data.recipes || []);
            renderPagination(pager, data.total, data.page, data.limit, (nextPage) => {
                currentPage = nextPage;
                fetchLiked(currentPage);
            });
        } catch {
            container.innerHTML = "<p>Failed to load liked recipes.</p>";
            pager.innerHTML = "";
        }
    }

    function renderRecipes(recipes) {
        container.innerHTML = "";
        if (!recipes.length) {
            container.innerHTML = "<p>No liked recipes.</p>";
            return;
        }

        const currentUserId = Number(window.currentUserId || 0);
        const isAdmin       = Boolean(window.isAdmin);

        recipes.forEach((r) => {
            const isOwner = Number(r.user_id) === currentUserId;

            const card = document.createElement("div");
            card.className = "recipe-card";
            card.dataset.userId = r.user_id;

            card.innerHTML = `
        <div class="meta">
          <span><i class="fa fa-user"></i> ${escapeHtml(r.username)}</span>
        </div>

        ${ (isOwner || isAdmin) ? `
          <div class="card-header">
            <a href="/profile/posts/edit.php?id=${r.id}" title="Edit"><i class="fas fa-edit"></i></a>
            <i class="fas fa-trash delete-icon" data-id="${r.id}" title="Delete"></i>
          </div>` : "" }

        <div class="image-container">
          <img src="/uploads/${escapeHtml(r.image_path)}" alt="Recipe image">
          <div class="like-container" data-id="${r.id}">
            <i class="fa fa-heart${r.liked ? " liked" : ""}"></i>
            <span class="like-count">${r.like_count}</span>
          </div>
        </div>

        <h3>${escapeHtml(r.name)}</h3>
        <p>${escapeHtml(r.description)}</p>
        <span class="category">${escapeHtml(r.category)}</span>
        <p class="date">${new Date(r.created_at).toLocaleDateString()}</p>

        <div class="view"><a href="/recipes/recipes.php?id=${r.id}">View</a></div>
      `;

            container.appendChild(card);
        });

        LikeClicker(container);
    }


    bindDeleteHandler(container, () => fetchLiked(currentPage));

    // старт
    fetchLiked(currentPage);
});
