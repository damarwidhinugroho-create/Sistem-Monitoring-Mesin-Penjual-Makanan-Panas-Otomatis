document.querySelectorAll('[data-period-picker]').forEach((picker) => {
    const trigger = picker.querySelector('[data-period-picker-trigger]');
    const panel = picker.querySelector('.period-picker__panel');

    if (!trigger || !panel) {
        return;
    }

    const close = () => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    };

    const selectOption = (attribute, value) => {
        picker.dataset[attribute] = value;
        picker.querySelectorAll(`[data-picker-${attribute}-option]`).forEach((button) => {
            const selected = button.dataset[`picker${attribute[0].toUpperCase()}${attribute.slice(1)}Option`] === value;
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
        picker.dispatchEvent(new CustomEvent('periodchange', { bubbles: true }));
    };

    trigger.addEventListener('click', () => {
        const opening = panel.hidden;
        panel.hidden = !opening;
        trigger.setAttribute('aria-expanded', String(opening));
    });

    picker.querySelectorAll('[data-picker-month-option]').forEach((button) => {
        button.addEventListener('click', () => selectOption('month', button.dataset.pickerMonthOption));
    });

    picker.querySelectorAll('[data-picker-year-option]').forEach((button) => {
        button.addEventListener('click', () => selectOption('year', button.dataset.pickerYearOption));
    });

    picker.querySelector('[data-clear-period]')?.addEventListener('click', () => {
        picker.dataset.month = '';
        picker.dataset.year = '';
        picker.querySelectorAll('[data-picker-month-option], [data-picker-year-option]').forEach((button) => {
            button.classList.remove('is-selected');
            button.setAttribute('aria-pressed', 'false');
        });
        picker.dispatchEvent(new CustomEvent('periodchange', { bubbles: true }));
        close();
        trigger.focus();
    });

    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) {
            close();
        }
    });

    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            close();
            trigger.focus();
        }
    });
});
