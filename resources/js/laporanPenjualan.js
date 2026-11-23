import { alertRecords, restockRecords, salesTransactions } from './data/mockLaporanData';
import { setButtonLoading, clearButtonLoading, showToast, positionPopover } from './interactions';

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
    const startDay = dateRangePicker.querySelector('[data-range-start-day]');
    const startMonth = dateRangePicker.querySelector('[data-range-start-month]');
    const startYear = dateRangePicker.querySelector('[data-range-start-year]');
    const endDay = dateRangePicker.querySelector('[data-range-end-day]');
    const endMonth = dateRangePicker.querySelector('[data-range-end-month]');
    const endYear = dateRangePicker.querySelector('[data-range-end-year]');
    const rangeError = dateRangePicker.querySelector('[data-range-error]');
    const hasCompleteRangeControls = startDay && startMonth && startYear && endDay && endMonth && endYear;

    if (hasCompleteRangeControls) {
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

        const getDaysInMonth = (year, month) => new Date(year, month, 0).getDate();

        const normalizeDayOptions = (daySelect, yearSelect, monthSelect) => {
            const year = Number(yearSelect.value);
            const month = Number(monthSelect.value);
            const maxDay = getDaysInMonth(year, month);
            const selectedDay = Number(daySelect.value);
            daySelect.querySelectorAll('option').forEach((option) => {
                option.hidden = Number(option.value) > maxDay;
            });
            if (selectedDay > maxDay) daySelect.value = String(maxDay);
        };

        const getSelectedDate = (daySelect, monthSelect, yearSelect) =>
            `${yearSelect.value}-${String(monthSelect.value).padStart(2, '0')}-${String(daySelect.value).padStart(2, '0')}`;

        const isValidCustomRange = () => {
            normalizeDayOptions(startDay, startYear, startMonth);
            normalizeDayOptions(endDay, endYear, endMonth);
            return getSelectedDate(startDay, startMonth, startYear) <= getSelectedDate(endDay, endMonth, endYear);
        };

        const updatePeriodLabel = () => {
            const labels = {
                day: 'Hari ini?',
                week: 'Minggu ini?',
                month: 'Bulan ini?',
            };
            periodLabel.textContent = activePeriod === 'custom'
                ? `Rentang ${formatDate(getSelectedDate(startDay, startMonth, startYear))} sampai ${formatDate(getSelectedDate(endDay, endMonth, endYear))}?`
                : labels[activePeriod];
        };

        const updateCustomRange = () => {
            normalizeDayOptions(startDay, startYear, startMonth);
            normalizeDayOptions(endDay, endYear, endMonth);
            const startValue = getSelectedDate(startDay, startMonth, startYear);
            const endValue = getSelectedDate(endDay, endMonth, endYear);
            dateRangePicker.dataset.startDate = startValue;
            dateRangePicker.dataset.endDate = endValue;

            const isValid = isValidCustomRange();
            const error = 'Tanggal Sampai tidak boleh lebih awal dari tanggal Dari.';
            rangeError.textContent = isValid ? '' : error;
            rangeError.hidden = isValid;
            [startDay, startMonth, startYear, endDay, endMonth, endYear].forEach((control) => {
                control.setAttribute('aria-invalid', String(!isValid));
            });
            confirmButton.disabled = !isValid;
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
                    start: parseLocalDate(getSelectedDate(startDay, startMonth, startYear)),
                    end: parseLocalDate(getSelectedDate(endDay, endMonth, endYear), true),
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
            if (!button) return;

            rangePanel.hidden = true;
            rangeTrigger.setAttribute('aria-expanded', 'false');
            setActivePeriod(button.dataset.period);
        });

        rangeTrigger.addEventListener('click', () => {
            setActivePeriod('custom');
            rangePanel.hidden = !rangePanel.hidden;
            rangeTrigger.setAttribute('aria-expanded', String(!rangePanel.hidden));
            if (!rangePanel.hidden) positionPopover(rangeTrigger, rangePanel);
        });

        const reflowRange = () => {
            if (!rangePanel.hidden) positionPopover(rangeTrigger, rangePanel);
        };
        window.addEventListener('resize', reflowRange);
        window.addEventListener('scroll', reflowRange, true);

        [startDay, startMonth, startYear, endDay, endMonth, endYear].forEach((control) => {
            control.addEventListener('change', updateCustomRange);
        });

        dateRangePicker.querySelector('[data-clear-period]')?.addEventListener('click', () => {
            const now = new Date();
            [startDay, endDay].forEach((control) => { control.value = String(now.getDate()); });
            [startMonth, endMonth].forEach((control) => { control.value = String(now.getMonth() + 1); });
            [startYear, endYear].forEach((control) => { control.value = String(now.getFullYear()); });
            updateCustomRange();
            setActivePeriod('day');
            rangePanel.hidden = true;
            rangeTrigger.setAttribute('aria-expanded', 'false');
        });

        document.addEventListener('click', (event) => {
            if (!dateRangePicker.contains(event.target) && !rangePanel.contains(event.target)) {
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
            if (event.target === dialog) dialog.close();
        });

        confirmButton.addEventListener('click', async () => {
            setButtonLoading(confirmButton, 'Mengekspor...');
            errorMessage.hidden = true;
            errorMessage.textContent = '';

            try {
                const { exportLaporanPenjualan } = await import('./utils/exportLaporanPenjualan');
                await exportLaporanPenjualan(getFilteredReport());
                dialog.close();
                showToast('Laporan berhasil diekspor.', 'success');
            } catch (error) {
                console.error('Gagal mengekspor laporan penjualan.', error);
                errorMessage.textContent = 'Ekspor gagal. Silakan coba lagi.';
                errorMessage.hidden = false;
                showToast('Ekspor laporan gagal.', 'error');
            } finally {
                clearButtonLoading(confirmButton, 'Ekspor');
            }
        });

        updatePeriodLabel();
        updateCustomRange();
    }
}
