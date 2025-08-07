export function bindDeleteHandler(container, refreshCallback) {
    container.addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-icon')) {
            const id = e.target.dataset.id;
            if (confirm('Are you sure you want to delete this recipe?')) {
                const res = await fetch(`/profile/posts/delete.php?id=${id}`, {
                    method: 'DELETE',
                    credentials: 'include',
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