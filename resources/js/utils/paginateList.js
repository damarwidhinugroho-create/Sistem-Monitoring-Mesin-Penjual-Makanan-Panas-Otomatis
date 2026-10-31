export function paginateList({ items, controls, count, pagination, viewAllButton, itemLabel, pageSize = 6 }) {
    if (!controls || !count || !pagination || !viewAllButton || items.length <= pageSize) {
        return;
    }

    let currentPage = 1;
    let showAll = false;
    controls.hidden = false;

    const render = () => {
        const pageCount = Math.ceil(items.length / pageSize);
        currentPage = Math.min(currentPage, pageCount);
        const start = showAll ? 0 : (currentPage - 1) * pageSize;
        const end = showAll ? items.length : Math.min(start + pageSize, items.length);

        items.forEach((item, index) => {
            item.hidden = index < start || index >= end;
        });

        count.textContent = showAll
            ? `Menampilkan semua ${items.length} ${itemLabel}`
            : `Menampilkan ${start + 1}–${end} dari ${items.length} ${itemLabel}`;
        viewAllButton.textContent = showAll ? 'Tampilkan per halaman' : 'Lihat semua';
        pagination.hidden = showAll;
        pagination.replaceChildren();

        if (showAll) {
            return;
        }

        const addPageButton = (label, page, { active = false, disabled = false, ariaLabel = '' } = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.dataset.page = String(page);
            button.disabled = disabled;

            if (active) {
                button.classList.add('is-current');
                button.setAttribute('aria-current', 'page');
            }
            if (ariaLabel) {
                button.setAttribute('aria-label', ariaLabel);
            }

            pagination.append(button);
        };

        addPageButton('<', currentPage - 1, {
            disabled: currentPage === 1,
            ariaLabel: 'Halaman sebelumnya',
        });
        for (let page = 1; page <= pageCount; page += 1) {
            addPageButton(String(page), page, { active: page === currentPage });
        }
        addPageButton('>', currentPage + 1, {
            disabled: currentPage === pageCount,
            ariaLabel: 'Halaman berikutnya',
        });
    };

    pagination.addEventListener('click', (event) => {
        const button = event.target instanceof Element
            ? event.target.closest('[data-page]')
            : null;

        if (!button || button.disabled) {
            return;
        }

        currentPage = Number(button.dataset.page);
        render();
    });

    viewAllButton.addEventListener('click', () => {
        showAll = !showAll;
        currentPage = 1;
        render();
    });

    render();
}
