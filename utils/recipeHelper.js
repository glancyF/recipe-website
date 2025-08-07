
export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function renderPagination(container, total, currentPage, limit, onPageChange) {
    const totalPages = Math.ceil(total / limit);
    container.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.disabled = i === currentPage;
        btn.addEventListener('click', () => onPageChange(i));
        container.appendChild(btn);
    }
}
