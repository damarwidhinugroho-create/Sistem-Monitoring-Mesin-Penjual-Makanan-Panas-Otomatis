import { alertRecords } from './data/mockLaporanData';

const root = document.querySelector('[data-alert-filter]');

if (root) {
    const tableBody = document.querySelector('.history-alert__table tbody');
    const emptyRow = tableBody?.querySelector('[data-alert-empty]');
    const pagination = document.querySelector('[data-alert-pagination]');
    const pageSize = 5;
    let selectedFilter = 'all';
    let currentPage = 1;
    let selectedMonth = '';
    let selectedYear = '';

    const formatTime = (value) => new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).format(new Date(value));

    const formatDateTime = (value) => {
        const date = new Date(value);
        const day = new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date);
        const month = new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(date);

        return `${day} ${month} - ${formatTime(value)}`;
    };

    const visibleAlerts = () => alertRecords
        .filter((alert) => {
            const occurredAt = new Date(alert.occurredAt);
            const matchesType = selectedFilter === 'all'
                || (selectedFilter === 'Unresolved' ? alert.status === 'Unresolved' : alert.priority === selectedFilter);

            return matchesType
                && (!selectedMonth || occurredAt.getMonth() + 1 === Number(selectedMonth))
                && (!selectedYear || occurredAt.getFullYear() === Number(selectedYear));
        })
        .sort((left, right) => new Date(right.occurredAt) - new Date(left.occurredAt));

    const makeCell = (className, text) => {
        const cell = document.createElement('td');
        cell.className = className;
        cell.textContent = text;
        return cell;
    };

    const createAlertRow = (alert) => {
        const row = document.createElement('tr');
        row.dataset.renderedAlert = '';

        const alertCell = document.createElement('td');
        const alertContent = document.createElement('div');
        alertContent.className = 'history-alert__alert-cell';

        [
            { className: 'history-alert__alert-type', text: `(${alert.type})` },
            { className: 'history-alert__alert-source', text: alert.source },
            { className: 'history-alert__alert-message', text: alert.message },
        ].forEach(({ className, text }) => {
            const line = document.createElement('span');
            line.className = className;
            line.textContent = text;
            alertContent.append(line);
        });
        alertCell.append(alertContent);
        row.append(alertCell);

        const timeCell = document.createElement('td');
        const timeContent = document.createElement('div');
        timeContent.className = 'history-alert__time-cell';
        const occurredAt = document.createElement('span');
        occurredAt.textContent = formatDateTime(alert.occurredAt);
        const resolvedAt = document.createElement('span');
        resolvedAt.textContent = `Selesai -${alert.resolvedAt ? ` ${formatTime(alert.resolvedAt)}` : ''}`;
        timeContent.append(occurredAt, resolvedAt);
        timeCell.append(timeContent);
        row.append(timeCell);

        const priorityCell = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = `badge badge--${alert.priority.toLowerCase()}`;
        const dot = document.createElement('span');
        dot.className = 'badge__dot';
        dot.setAttribute('aria-hidden', 'true');
        const priority = document.createElement('span');
        priority.textContent = alert.priority;
        badge.append(dot, priority);
        priorityCell.append(badge);
        row.append(priorityCell);

        const statusClass = alert.status === 'Resolved' ? 'resolved' : 'unresolved';
        row.append(makeCell(`history-alert__status--${statusClass}`, alert.status));
        return row;
    };

    const renderPagination = (pageCount) => {
        pagination.replaceChildren();
        pagination.hidden = pageCount <= 1;

        if (pageCount <= 1) {
            return;
        }

        const addButton = (label, page, { current = false, disabled = false, ariaLabel = '' } = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.dataset.page = String(page);
            button.disabled = disabled;

            if (current) {
                button.classList.add('is-current');
                button.setAttribute('aria-current', 'page');
            }
            if (ariaLabel) {
                button.setAttribute('aria-label', ariaLabel);
            }

            pagination.append(button);
        };

        addButton('<', currentPage - 1, { disabled: currentPage === 1, ariaLabel: 'Halaman sebelumnya' });
        const visiblePages = pageCount <= 4
            ? Array.from({ length: pageCount }, (_, index) => index + 1)
            : [...new Set([1, 2, 3, ...(currentPage > 3 && currentPage < pageCount ? [currentPage] : []), pageCount])];

        visiblePages.forEach((page, index) => {
            if (index > 0 && page - visiblePages[index - 1] > 1) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.setAttribute('aria-hidden', 'true');
                pagination.append(ellipsis);
            }
            addButton(String(page), page, { current: currentPage === page });
        });
        addButton('>', currentPage + 1, { disabled: currentPage === pageCount, ariaLabel: 'Halaman berikutnya' });
    };

    const render = () => {
        if (!tableBody || !emptyRow || !pagination) {
            return;
        }

        const alerts = visibleAlerts();
        const pageCount = Math.ceil(alerts.length / pageSize);
        currentPage = Math.min(currentPage, Math.max(pageCount, 1));
        const pageAlerts = alerts.slice((currentPage - 1) * pageSize, currentPage * pageSize);
        tableBody.replaceChildren();

        if (pageAlerts.length === 0) {
            emptyRow.hidden = false;
            tableBody.append(emptyRow);
        } else {
            emptyRow.hidden = true;
            pageAlerts.forEach((alert) => tableBody.append(createAlertRow(alert)));
        }
        renderPagination(pageCount);
    };

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-filter]');

        if (!button) {
            return;
        }

        selectedFilter = button.dataset.filter;
        currentPage = 1;
        root.querySelectorAll('[data-filter]').forEach((filterButton) => {
            const active = filterButton === button;
            filterButton.classList.toggle('is-active', active);
            filterButton.setAttribute('aria-pressed', String(active));
        });
        render();
    });

    document.querySelector('[data-period-picker][data-context="alerts"]')?.addEventListener('periodchange', (event) => {
        selectedMonth = event.currentTarget.dataset.month;
        selectedYear = event.currentTarget.dataset.year;
        currentPage = 1;
        render();
    });

    pagination?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-page]');

        if (!button || button.disabled) {
            return;
        }

        currentPage = Number(button.dataset.page);
        render();
    });

    render();
}
