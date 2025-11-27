export function bindDeleteHandler(container, refreshCallback) {
    container.addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-icon')) {
            const id = e.target.dataset.id;
            if (confirm('Are you sure you want to delete this recipe?')) {
                const res = await fetch(`profile/posts/delete.php`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id }),
                });
                const result = await res.json();
                if (result.status === 'success') {
                    refreshCallback();
                } else {
                    alert('Failed to delete recipe');
                }
            }
        }
    });
}



export function buildRecipeCardHTML(recipe, { isOwner, isAdmin, showTrophies=false, rank=null }) {
    let trophyClass = '';
    if (showTrophies && rank != null) {
        if (rank === 1) trophyClass = 'trophy-gold';
        else if (rank === 2) trophyClass = 'trophy-silver';
        else if (rank === 3) trophyClass = 'trophy-bronze';
    }

    return `
    <div class="meta">
      <span><i class="fa fa-user"></i> ${escapeHtml(recipe.username)}</span>
      ${trophyClass ? `<span><i class="fa-solid fa-trophy ${trophyClass}"></i></span>` : ''}
    </div>
    <div class="image-container">
      <img src="uploads/card/${escapeHtml(recipe.image_path)}" alt="Recipe image">
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
          <a href="profile/posts/edit.php?id=${recipe.id}" title="Edit"><i class="fas fa-edit"></i></a>
          <i class="fas fa-trash delete-icon" data-id="${recipe.id}" title="Delete"></i>
        </div>` : `<div></div>`}
      <div class="view"><a href="recipes/recipes.php?id=${recipe.id}">View</a></div>
    </div>
  `;
}



export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function renderPagination(container, total, currentPage, limit, onPageChange) {
    if (!container) { console.warn('renderPagination: container not found'); return; }

    const _total       = Number(total ?? 0);
    const _limit       = Math.max(1, Number(limit ?? 10));
    const _currentPage = Math.max(1, Number(currentPage ?? 1));
    const totalPages   = Math.max(1, Math.ceil(_total / _limit));

    container.innerHTML = '';
    container.classList.add('pagination-controls');

    const btn = (label, disabled, handler) => {
        const b = document.createElement('button');
        b.textContent = label;
        b.disabled = !!disabled;
        if (handler) b.addEventListener('click', handler);
        return b;
    };


    container.appendChild(
        btn('Prev', _currentPage <= 1, () => onPageChange(_currentPage - 1))
    );

    const info = document.createElement('span');
    info.className = 'page-indicator';
    info.textContent = `Page ${_currentPage} of ${totalPages}`;
    container.appendChild(info);

    const jump = document.createElement('span');
    jump.className = 'jump-wrap';

    const input = document.createElement('input');
    input.type = 'number';
    input.min = '1';
    input.max = String(totalPages);
    input.placeholder = 'Page…';

    const goBtn = btn('Go', false, go);
    function go() {
        const n = Math.max(1, Math.min(totalPages, Number(input.value)));
        if (Number.isInteger(n) && n !== _currentPage) onPageChange(n);
    }
    input.addEventListener('keydown', (e) => e.key === 'Enter' && go());

    jump.appendChild(input);
    jump.appendChild(goBtn);
    container.appendChild(jump);

    container.appendChild(
        btn('Next', _currentPage >= totalPages, () => onPageChange(_currentPage + 1))
    );
}

export function LikeClicker(container){
    container.addEventListener('click',async (e) => {
        if (e.target.classList.contains('fa-heart')) {
            const likeIcon = e.target;
            const card = likeIcon.closest('.like-container');
            const recipeId = card.dataset.id;
            const res = await fetch(`likes/toggle_like.php?id=${recipeId}`, {
                method: 'POST',
                credentials: 'include'
            });
            const result = await res.json();
            if (result.status === 'success') {
                likeIcon.classList.toggle('liked', result.liked);
                card.querySelector('.like-count').textContent = result.like_count;
            } else {
                window.location.href='login/auth.php';
                alert('Failed to like the recipe, if you unauthorized or don`t have account yet, please log in or create an account');
            }
        }
    });
}