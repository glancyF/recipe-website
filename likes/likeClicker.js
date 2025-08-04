export function LikeClicker(container){
    container.addEventListener('click',async (e) => {
        if (e.target.classList.contains('fa-heart')) {
            const likeIcon = e.target;
            const card = likeIcon.closest('.like-container');
            const recipeId = card.dataset.id;
            const res = await fetch(`/likes/toggle_like.php?id=${recipeId}`, {
                method: 'POST',
                credentials: 'include'
            });
            const result = await res.json();
            if (result.status === 'success') {
                likeIcon.classList.toggle('liked', result.liked);
                card.querySelector('.like-count').textContent = result.like_count;
            } else {
                alert('Failed to like the recipe, if you unauthorized or don`t have account yet, please log in or create an account');
            }
        }
    });
}