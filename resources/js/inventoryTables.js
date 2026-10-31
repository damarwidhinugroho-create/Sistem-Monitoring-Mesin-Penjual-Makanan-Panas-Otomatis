import { paginateList } from './utils/paginateList';

document.querySelectorAll('[data-paginated-list]').forEach((list) => {
    const rows = Array.from(list.querySelectorAll('[data-pagination-row]'));
    paginateList({
        items: rows,
        controls: list.querySelector('[data-pagination-controls]'),
        count: list.querySelector('[data-pagination-count]'),
        pagination: list.querySelector('[data-pagination-nav]'),
        viewAllButton: list.querySelector('[data-pagination-view-all]'),
        itemLabel: list.dataset.itemLabel ?? 'item',
    });
});
