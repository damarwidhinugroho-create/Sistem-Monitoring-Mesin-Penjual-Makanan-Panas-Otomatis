import { paginateList } from './utils/paginateList';

const slotGrid = document.querySelector('.inventory__slot-grid');

if (slotGrid) {
    const cards = Array.from(slotGrid.querySelectorAll('[data-slot-card]'));
    paginateList({
        items: cards,
        controls: document.querySelector('[data-slot-controls]'),
        count: document.querySelector('[data-slot-count]'),
        pagination: document.querySelector('[data-slot-pagination]'),
        viewAllButton: document.querySelector('[data-slot-view-all]'),
        itemLabel: 'slot',
    });
}
