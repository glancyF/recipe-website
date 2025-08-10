
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

    // Prev
    container.appendChild(
        btn('Prev', _currentPage <= 1, () => onPageChange(_currentPage - 1))
    );

    // "Page X of Y"
    const info = document.createElement('span');
    info.className = 'page-indicator';
    info.textContent = `Page ${_currentPage} of ${totalPages}`;
    container.appendChild(info);

    // Go to page
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

    // Next
    container.appendChild(
        btn('Next', _currentPage >= totalPages, () => onPageChange(_currentPage + 1))
    );
}

