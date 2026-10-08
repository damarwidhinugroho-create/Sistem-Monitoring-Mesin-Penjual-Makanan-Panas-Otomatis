import { alertRecords, restockRecords, salesTransactions } from './data/mockLaporanData';

const periodNav = document.querySelector('[data-report-period]');
const dialog = document.querySelector('[data-export-dialog]');
const periodLabel = document.querySelector('[data-export-period-label]');
const confirmButton = document.querySelector('[data-confirm-export]');
const errorMessage = document.querySelector('[data-export-error]');
const dateRangePicker = document.querySelector('[data-date-range-picker]');

if (periodNav && dialog && periodLabel && confirmButton && errorMessage && dateRangePicker) {
    let activePeriod = 'day';
    const rangeTrigger = dateRangePicker.querySelector('[data-date-range-trigger]');
    const rangePanel = dateRangePicker.querySelector('.date-range-picker__panel');
    const startInput = dateRangePicker.querySelector('[data-range-start]');
    const endInput = dateRangePicker.querySelector('[data-range-end]');
    const rangeError = dateRangePicker.querySelector('[data-range-error]');

    const periodNames = {
        day: 'Hari',
        week: 'Minggu',
        month: 'Bulan',
        custom: 'Custom',
    };

    const parseLocalDate = (value, endOfDay = false) => {
        const [year, month, day] = value.split('-').map(Number);
        const date = new Date(year, month - 1, day);
        date.setHours(endOfDay ? 23 : 0, endOfDay ? 59 : 0, endOfDay ? 59 : 0, endOfDay ? 999 : 0);
        return date;
    };

    const formatDate = (value) => {
        if (!value) {
            return '';
        }

        const [year, month, day] = value.split('-');
        return `${day}/${month}/${year}`;
    };

    const isValidCustomRange = () => Boolean(startInput.value && endInput.value && startInput.value <= endInput.value);

    const updateCustomRange = () => {
        dateRangePicker.dataset.startDate = startInput.value;
        dateRangePicker.dataset.endDate = endInput.value;
        endInput.min = startInput.value;
        startInput.max = endInput.value;

        const isValid = isValidCustomRange();
        const error = startInput.value && endInput.value
            ? 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.'
            : 'Pilih tanggal mulai dan tanggal akhir.';
        rangeError.textContent = isValid ? '' : error;
        rangeError.hidden = isValid;
        startInput.setAttribute('aria-invalid', String(!isValid));
        endInput.setAttribute('aria-invalid', String(!isValid));
        updatePeriodLabel();
    };

    const getPeriodRange = () => {
        const now = new Date();
        const start = new Date(now);
        const end = new Date(now);
        start.setHours(0, 0, 0, 0);
        end.setHours(23, 59, 59, 999);

        if (activePeriod === 'week') {
            const daysSinceMonday = (now.getDay() + 6) % 7;
            start.setDate(now.getDate() - daysSinceMonday);
            end.setTime(start.getTime());
            end.setDate(start.getDate() + 6);
            end.setHours(23, 59, 59, 999);
        } else if (activePeriod === 'month') {
            start.setDate(1);
            end.setMonth(now.getMonth() + 1, 0);
            end.setHours(23, 59, 59, 999);
        } else if (activePeriod === 'custom') {
            if (!isValidCustomRange()) {
                throw new Error('Pilih rentang tanggal yang valid sebelum mengekspor laporan.');
            }
            return {
                start: parseLocalDate(startInput.value),
                end: parseLocalDate(endInput.value, true),
            };
        }

        return { start, end };
    };

    const getFilteredReport = () => {
        const { start, end } = getPeriodRange();
        const inRange = (record) => {
            const date = new Date(record.occurredAt);
            return date >= start && date <= end;
        };

        return {
            period: periodNames[activePeriod],
            startDate: start,
            endDate: end,
            sales: salesTransactions.filter(inRange),
            restocks: restockRecords.filter(inRange),
            alerts: alertRecords.filter(inRange),
        };
    };

    const updatePeriodLabel = () => {
        const labels = {
            day: 'Hari ini?',
            week: 'Minggu ini?',
            month: 'Bulan ini?',
        };
        periodLabel.textContent = activePeriod === 'custom'
            ? `Rentang ${formatDate(startInput.value)} sampai ${formatDate(endInput.value)}?`
            : labels[activePeriod];
    };

    const setActivePeriod = (period) => {
        activePeriod = period;
        periodNav.querySelectorAll('[data-period]').forEach((button) => {
            const active = button.dataset.period === activePeriod;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        rangeTrigger.classList.toggle('is-active', activePeriod === 'custom');
        updatePeriodLabel();
    };

    periodNav.addEventListener('click', (event) => {
        const button = event.target.closest('[data-period]');

        if (button) {
            rangePanel.hidden = true;
            rangeTrigger.setAttribute('aria-expanded', 'false');
            setActivePeriod(button.dataset.period);
        }
    });

    rangeTrigger.addEventListener('click', () => {
        setActivePeriod('custom');
        rangePanel.hidden = !rangePanel.hidden;
        rangeTrigger.setAttribute('aria-expanded', String(!rangePanel.hidden));
    });

    [startInput, endInput].forEach((input) => input.addEventListener('change', updateCustomRange));

    document.addEventListener('click', (event) => {
        if (!dateRangePicker.contains(event.target)) {
            rangePanel.hidden = true;
            rangeTrigger.setAttribute('aria-expanded', 'false');
        }
    });

    dateRangePicker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !rangePanel.hidden) {
            rangePanel.hidden = true;
            rangeTrigger.setAttribute('aria-expanded', 'false');
            rangeTrigger.focus();
        }
    });

    document.querySelector('[data-open-export]')?.addEventListener('click', () => {
        errorMessage.hidden = true;
        errorMessage.textContent = '';
        updatePeriodLabel();
        dialog.showModal();
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    confirmButton.addEventListener('click', async () => {
        confirmButton.disabled = true;
        confirmButton.textContent = 'Mengekspor...';
        errorMessage.hidden = true;
        errorMessage.textContent = '';

        try {
            const { exportLaporanPenjualan } = await import('./utils/exportLaporanPenjualan');
            await exportLaporanPenjualan(getFilteredReport());
            dialog.close();
        } catch (error) {
            console.error('Gagal mengekspor laporan penjualan.', error);
            errorMessage.textContent = 'Ekspor gagal. Silakan coba lagi.';
            errorMessage.hidden = false;
        } finally {
            confirmButton.disabled = false;
            confirmButton.textContent = 'Ekspor';
        }
    });

    updatePeriodLabel();
    updateCustomRange();
}
