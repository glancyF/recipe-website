import { LikeClicker } from "../../likes/likeClicker.js";
import { escapeHtml, renderPagination } from "../../utils/recipeHelper.js";
import { bindDeleteHandler } from "../../utils/deleHandler.js";

document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("recipesContainer");
    const pager     = document.getElementById("pagination");

    let currentPage = 1;

    async function fetchRecipes(page = 1) {
        try {
            const res  = await fetch(`/profile/posts/post.php?page=${page}`, { credentials: "include" });
            const data = await res.json();

            if (data.status !== "success") {
                container.innerHTML = "<p>Failed to load recipes.</p>";
                pager.innerHTML = "";
                return;
            }

            renderRecipes(data.recipes || []);
            renderPagination(pager, data.total, data.page, data.limit, (nextPage) => {
                currentPage = nextPage;
                fetchRecipes(currentPage);
            });
        } catch {
            container.innerHTML = "<p>Failed to load recipes.</p>";
            pager.innerHTML = "";
        }
    }

    function renderRecipes(recipes) {
        container.innerHTML = "";
        if (!recipes.length) {
            container.innerHTML = "<p>No recipes found.</p>";
            return;
        }

        recipes.forEach((r) => {
            const card = document.createElement("div");
            card.className = "recipe-card";

            card.innerHTML = `
        <div class="card-header">
          <a href="/profile/posts/edit.php?id=${r.id}" title="Edit"><i class="fas fa-edit"></i></a>
          <i class="fas fa-trash delete-icon" data-id="${r.id}" title="Delete"></i>
        </div>

        <div class="image-container">
          <img src="/uploads/${escapeHtml(r.image_path)}" alt="Image">
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

    bindDeleteHandler(container, () => fetchRecipes(currentPage));

    fetchRecipes(currentPage);
});
