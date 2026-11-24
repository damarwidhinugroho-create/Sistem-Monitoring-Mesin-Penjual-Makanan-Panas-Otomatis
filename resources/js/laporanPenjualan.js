import { clearButtonLoading, positionPopover, setButtonLoading, showToast } from './interactions';

const reportPage = document.querySelector('[data-report-page]');
const periodNav = reportPage?.querySelector('[data-report-period]');
const dateRangePicker = reportPage?.querySelector('[data-date-range-picker]');
const exportButton = reportPage?.querySelector('[data-open-export]');
const dialog = document.querySelector('[data-export-dialog]');
const confirmButton = dialog?.querySelector('[data-confirm-export]');
const cancelButton = dialog?.querySelector('[data-cancel-export]');
const errorMessage = dialog?.querySelector('[data-export-error]');
const periodLabel = dialog?.querySelector('[data-export-period-label]');
const emptyExportNote = dialog?.querySelector('[data-export-empty-note]');
const historyError = reportPage?.querySelector('[data-history-error]');
const historyTableBody = reportPage?.querySelector('[data-report-history-table] tbody');
const historyFooter = reportPage?.querySelector('[data-report-history-footer]');
const historyCount = reportPage?.querySelector('[data-report-history-count]');
const historyPagination = reportPage?.querySelector('[data-report-history-pagination]');

if (reportPage && periodNav && dateRangePicker && exportButton && dialog && confirmButton && cancelButton && errorMessage && periodLabel && historyError && historyTableBody && historyFooter && historyCount && historyPagination) {
    const maxCustomRangeDays = 31;
    const wibOffsetMilliseconds = 7 * 60 * 60 * 1000;
    const pageSize = 10;
    const exportHistoryStorageKey = 'operator-sales-report:history:v1';
    const lastExportStorageKey = 'operator-sales-report:last-export:v1';
    const rangeTrigger = dateRangePicker.querySelector('[data-date-range-trigger]');
    const rangePanel = dateRangePicker.querySelector('.date-range-picker__panel');
    const startDay = dateRangePicker.querySelector('[data-range-start-day]');
    const startMonth = dateRangePicker.querySelector('[data-range-start-month]');
    const startYear = dateRangePicker.querySelector('[data-range-start-year]');
    const endDay = dateRangePicker.querySelector('[data-range-end-day]');
    const endMonth = dateRangePicker.querySelector('[data-range-end-month]');
    const endYear = dateRangePicker.querySelector('[data-range-end-year]');
    const rangeError = dateRangePicker.querySelector('[data-range-error]');
    const dateControls = [startDay, startMonth, startYear, endDay, endMonth, endYear];
    let activePeriod = 'day';

    const reportRecords = {
        sales: [],
        restocks: [],
        alerts: [],
    };
    let exportHistory = [];
    let historyPage = 1;

    const getWibDateParts = (date = new Date()) => {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(date);
        return Object.fromEntries(parts.map(({ type, value }) => [type, value]));
    };

    const getTodayKey = () => {
        const { year, month, day } = getWibDateParts();
        return `${year}-${month}-${day}`;
    };

    const getDateKey = (dayControl, monthControl, yearControl) =>
        `${yearControl.value}-${String(monthControl.value).padStart(2, '0')}-${String(dayControl.value).padStart(2, '0')}`;

    const formatDateKey = (key) => {
        const [year, month, day] = key.split('-');
        return `${day}/${month}/${year}`;
    };

    const isValidExportEntry = (entry) => {
        const validDateKey = (value) => typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value);
        const validRecords = (value) =>
            Array.isArray(value) && value.every((record) => record && typeof record === 'object' && !Array.isArray(record));
        return Boolean(
            entry
            && entry.version === 1
            && typeof entry.id === 'string'
            && typeof entry.exportedAt === 'string'
            && typeof entry.period === 'string'
            && validDateKey(entry.startDateKey)
            && validDateKey(entry.endDateKey)
            && validRecords(entry.sales)
            && validRecords(entry.restocks)
            && validRecords(entry.alerts)
        );
    };

    const createExportId = () => `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;

    const readExportHistory = () => {
        const savedHistory = window.localStorage.getItem(exportHistoryStorageKey);
        if (savedHistory !== null) {
            const parsedHistory = JSON.parse(savedHistory);
            if (!Array.isArray(parsedHistory) || !parsedHistory.every(isValidExportEntry)) {
                throw new Error('Format riwayat ekspor di browser tidak valid.');
            }
            return parsedHistory;
        }

        const legacyExport = window.localStorage.getItem(lastExportStorageKey);
        if (!legacyExport) return [];

        const parsedExport = JSON.parse(legacyExport);
        const migratedExport = parsedExport && {
            ...parsedExport,
            id: parsedExport.id ?? createExportId(),
        };
        if (!isValidExportEntry(migratedExport)) {
            throw new Error('Format riwayat ekspor sebelumnya tidak valid.');
        }
        return [migratedExport];
    };

    const persistExport = (report) => {
        const entry = {
            version: 1,
            id: createExportId(),
            exportedAt: new Date().toISOString(),
            period: report.period,
            startDateKey: report.startDateKey,
            endDateKey: report.endDateKey,
            sales: report.sales,
            restocks: report.restocks,
            alerts: report.alerts,
        };
        const updatedHistory = [entry, ...exportHistory];
        window.localStorage.setItem(exportHistoryStorageKey, JSON.stringify(updatedHistory));
        return updatedHistory;
    };

    const dateKeyToUtcDay = (key) => {
        const [year, month, day] = key.split('-').map(Number);
        return Date.UTC(year, month - 1, day);
    };

    const dateKeyToWibInstant = (key, endOfDay = false) => {
        const [year, month, day] = key.split('-').map(Number);
        const hours = endOfDay ? 23 : 0;
        const minutes = endOfDay ? 59 : 0;
        const seconds = endOfDay ? 59 : 0;
        const milliseconds = endOfDay ? 999 : 0;
        return new Date(Date.UTC(year, month - 1, day, hours, minutes, seconds, milliseconds) - wibOffsetMilliseconds);
    };

    const getDaysInMonth = (year, month) => new Date(Date.UTC(year, month, 0)).getUTCDate();

    const normalizeDayOptions = (dayControl, yearControl, monthControl) => {
        const maxDay = getDaysInMonth(Number(yearControl.value), Number(monthControl.value));
        const selectedDay = Number(dayControl.value);
        dayControl.querySelectorAll('option').forEach((option) => {
            option.hidden = Number(option.value) > maxDay;
        });
        if (selectedDay > maxDay) dayControl.value = String(maxDay);
    };

    const validateCustomRange = () => {
        normalizeDayOptions(startDay, startYear, startMonth);
        normalizeDayOptions(endDay, endYear, endMonth);

        const startKey = getDateKey(startDay, startMonth, startYear);
        const endKey = getDateKey(endDay, endMonth, endYear);
        const todayKey = getTodayKey();
        let error = '';

        if (endKey < startKey) {
            error = 'Tanggal akhir tidak boleh sebelum tanggal mulai.';
        } else if (startKey > todayKey || endKey > todayKey) {
            error = 'Rentang tanggal tidak boleh berada di masa depan.';
        } else {
            const rangeDays = (dateKeyToUtcDay(endKey) - dateKeyToUtcDay(startKey)) / 86400000 + 1;
            if (rangeDays > maxCustomRangeDays) {
                error = `Rentang tanggal maksimal ${maxCustomRangeDays} hari.`;
            }
        }

        return { valid: error === '', error, startKey, endKey };
    };

    const setRangeError = ({ valid, error }) => {
        rangeError.textContent = error;
        rangeError.hidden = valid;
        dateControls.forEach((control) => control.setAttribute('aria-invalid', String(!valid)));
        if (activePeriod === 'custom') exportButton.disabled = !valid;
        if (activePeriod === 'custom') confirmButton.disabled = !valid;
    };

    const getPeriodRange = () => {
        const todayKey = getTodayKey();
        let startKey = todayKey;
        let endKey = todayKey;

        if (activePeriod === 'week') {
            const todayDate = new Date(dateKeyToUtcDay(todayKey));
            const daysSinceMonday = (todayDate.getUTCDay() + 6) % 7;
            const monday = new Date(todayDate.getTime() - daysSinceMonday * 86400000);
            const sunday = new Date(monday.getTime() + 6 * 86400000);
            startKey = monday.toISOString().slice(0, 10);
            endKey = sunday.toISOString().slice(0, 10);
        } else if (activePeriod === 'month') {
            const [year, month] = todayKey.split('-').map(Number);
            startKey = `${year}-${String(month).padStart(2, '0')}-01`;
            endKey = `${year}-${String(month).padStart(2, '0')}-${String(getDaysInMonth(year, month)).padStart(2, '0')}`;
        } else if (activePeriod === 'custom') {
            const range = validateCustomRange();
            return { ...range, startDate: dateKeyToWibInstant(range.startKey), endDate: dateKeyToWibInstant(range.endKey, true) };
        }

        return {
            startKey,
            endKey,
            startDate: dateKeyToWibInstant(startKey),
            endDate: dateKeyToWibInstant(endKey, true),
        };
    };

    const formatWibDateTime = (value) => {
        if (!value) return '—';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        }).format(date);
    };

    const formatRupiah = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value) || 0)}`;

    const getFilteredReport = () => {
        const range = getPeriodRange();
        const isInRange = (record) => {
            const timestamp = new Date(record.occurredAt).getTime();
            return Number.isFinite(timestamp) && timestamp >= range.startDate.getTime() && timestamp <= range.endDate.getTime();
        };

        return {
            period: activePeriod === 'custom' ? 'Custom' : ({ day: 'Hari', week: 'Minggu', month: 'Bulan' })[activePeriod],
            startDateKey: range.startKey,
            endDateKey: range.endKey,
            startDate: range.startDate,
            endDate: range.endDate,
            sales: reportRecords.sales.filter(isInRange),
            restocks: reportRecords.restocks.filter(isInRange),
            alerts: reportRecords.alerts.filter(isInRange),
        };
    };

    const setMetric = (name, value, detail) => {
        const metric = reportPage.querySelector(`[data-report-metric="${name}"]`);
        metric.querySelector('.stat-card__value').textContent = value;
        metric.querySelector('.stat-card__detail').textContent = detail;
    };

    const updatePreview = () => {
        const pageCount = Math.ceil(exportHistory.length / pageSize);
        historyPage = Math.max(1, Math.min(historyPage, pageCount || 1));
        const visibleEntries = exportHistory.slice((historyPage - 1) * pageSize, historyPage * pageSize);
        historyTableBody.replaceChildren();

        if (visibleEntries.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 6;
            cell.className = 'report__empty';
            cell.textContent = 'Belum ada riwayat ekspor laporan.';
            row.append(cell);
            historyTableBody.append(row);
        } else {
            visibleEntries.forEach((entry) => {
                const row = document.createElement('tr');
                const totalRevenue = entry.sales.reduce((sum, sale) => sum + (Number(sale.total) || 0), 0);
                const values = [
                    formatWibDateTime(entry.exportedAt),
                    entry.period,
                    `${formatDateKey(entry.startDateKey)} – ${formatDateKey(entry.endDateKey)}`,
                    new Intl.NumberFormat('id-ID').format(entry.sales.length),
                    formatRupiah(totalRevenue),
                ];

                values.forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.append(cell);
                });

                const actionCell = document.createElement('td');
                const downloadButton = document.createElement('button');
                downloadButton.type = 'button';
                downloadButton.className = 'report__history-download';
                downloadButton.dataset.historyDownload = entry.id;
                downloadButton.textContent = 'Unduh ulang';
                downloadButton.setAttribute(
                    'aria-label',
                    `Unduh ulang laporan ${formatDateKey(entry.startDateKey)} sampai ${formatDateKey(entry.endDateKey)}`,
                );
                actionCell.append(downloadButton);
                row.append(actionCell);
                historyTableBody.append(row);
            });
        }

        historyFooter.hidden = exportHistory.length <= pageSize;
        historyCount.textContent = `Menampilkan ${(historyPage - 1) * pageSize + 1}–${Math.min(historyPage * pageSize, exportHistory.length)} dari ${exportHistory.length} laporan`;
        historyPagination.replaceChildren();
        if (pageCount <= 1) return;

        const addPageButton = (label, page, { active = false, disabled = false, ariaLabel = '' } = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.dataset.historyPage = String(page);
            button.disabled = disabled;
            if (active) button.setAttribute('aria-current', 'page');
            if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
            historyPagination.append(button);
        };

        addPageButton('‹', historyPage - 1, { disabled: historyPage === 1, ariaLabel: 'Halaman sebelumnya' });
        for (let page = 1; page <= pageCount; page += 1) {
            addPageButton(String(page), page, { active: page === historyPage });
        }
        addPageButton('›', historyPage + 1, { disabled: historyPage === pageCount, ariaLabel: 'Halaman berikutnya' });
    };

    const updateReport = () => {
        const latestExport = exportHistory[0];
        const sales = latestExport?.sales ?? [];
        const restocks = latestExport?.restocks ?? [];
        const detail = latestExport ? 'Data Saat Ini' : 'Belum ada ekspor sebelumnya';
        const totalRevenue = sales.reduce((sum, record) => sum + (Number(record.total) || 0), 0);
        const totalQuantity = sales.reduce((sum, record) => sum + (Number(record.quantity) || 0), 0);
        setMetric('transactions', String(sales.length), detail);
        setMetric('revenue', formatRupiah(totalRevenue), detail);
        setMetric('quantity', new Intl.NumberFormat('id-ID').format(totalQuantity), detail);
        setMetric('restocks', String(restocks.length), detail);
    };

    const updateCustomRange = () => {
        const range = validateCustomRange();
        dateRangePicker.dataset.startDate = range.startKey;
        dateRangePicker.dataset.endDate = range.endKey;
        setRangeError(range);
        if (range.valid && activePeriod === 'custom') updateReport();
    };

    const setActivePeriod = (period) => {
        activePeriod = period;
        periodNav.querySelectorAll('[data-period]').forEach((button) => {
            const active = button.dataset.period === activePeriod;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        rangeTrigger.classList.toggle('is-active', activePeriod === 'custom');
        if (activePeriod === 'custom') {
            updateCustomRange();
        } else {
            exportButton.disabled = false;
            confirmButton.disabled = false;
            updateReport();
        }
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
        const opening = rangePanel.hidden;
        rangePanel.hidden = !opening;
        rangeTrigger.setAttribute('aria-expanded', String(opening));
        if (opening) positionPopover(rangeTrigger, rangePanel);
    });

    const reflowRange = () => {
        if (!rangePanel.hidden) positionPopover(rangeTrigger, rangePanel);
    };
    window.addEventListener('resize', reflowRange);
    window.addEventListener('scroll', reflowRange, true);

    dateControls.forEach((control) => control.addEventListener('change', updateCustomRange));
    dateRangePicker.querySelector('[data-clear-period]')?.addEventListener('click', () => {
        const [year, month, day] = getTodayKey().split('-').map(Number);
        [startDay, endDay].forEach((control) => { control.value = String(day); });
        [startMonth, endMonth].forEach((control) => { control.value = String(month); });
        [startYear, endYear].forEach((control) => { control.value = String(year); });
        updateCustomRange();
        rangePanel.hidden = true;
        rangeTrigger.setAttribute('aria-expanded', 'false');
        setActivePeriod('day');
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

    historyPagination.addEventListener('click', (event) => {
        const button = event.target.closest('[data-history-page]');
        if (!button || button.disabled) return;
        historyPage = Number(button.dataset.historyPage);
        updatePreview();
    });

    historyTableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-history-download]');
        if (!button) return;

        const entry = exportHistory.find((report) => report.id === button.dataset.historyDownload);
        if (!entry) {
            showToast('Riwayat laporan tidak ditemukan.', 'error');
            return;
        }
        if (!setButtonLoading(button, 'Menyiapkan...')) return;

        try {
            const { exportLaporanPenjualan } = await import('./utils/exportLaporanPenjualan');
            await exportLaporanPenjualan({
                ...entry,
                startDate: dateKeyToWibInstant(entry.startDateKey),
                endDate: dateKeyToWibInstant(entry.endDateKey, true),
            });
            showToast('Laporan berhasil diunduh ulang.', 'success');
        } catch (error) {
            console.error('Gagal mengunduh ulang laporan dari riwayat.', error);
            showToast('Laporan gagal diunduh ulang.', 'error');
        } finally {
            clearButtonLoading(button, 'Unduh ulang');
        }
    });

    exportButton.addEventListener('click', () => {
        const report = getFilteredReport();
        errorMessage.hidden = true;
        errorMessage.textContent = '';
        periodLabel.textContent = `Periode: ${formatDateKey(report.startDateKey)} – ${formatDateKey(report.endDateKey)}`;
        emptyExportNote.hidden = report.sales.length + report.restocks.length + report.alerts.length > 0;
        dialog.showModal();
    });

    cancelButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    confirmButton.addEventListener('click', async () => {
        if (activePeriod === 'custom' && !validateCustomRange().valid) return;
        if (!setButtonLoading(confirmButton, 'Mengekspor...')) return;
        errorMessage.hidden = true;
        errorMessage.textContent = '';

        try {
            let report;
            try {
                report = getFilteredReport();
                const { exportLaporanPenjualan } = await import('./utils/exportLaporanPenjualan');
                await exportLaporanPenjualan(report);
            } catch (error) {
                console.error('Gagal mengekspor laporan penjualan.', error);
                errorMessage.textContent = 'Ekspor gagal. Silakan coba lagi.';
                errorMessage.hidden = false;
                showToast('Ekspor laporan gagal.', 'error');
                return;
            }

            dialog.close();
            try {
                exportHistory = persistExport(report);
                historyPage = 1;
                historyError.hidden = true;
                historyError.textContent = '';
                updatePreview();
                updateReport();
                showToast('Laporan berhasil diunduh.', 'success');
            } catch (error) {
                console.error('Laporan sudah diunduh, tetapi riwayat ekspor tidak dapat disimpan.', error);
                historyError.textContent = 'File berhasil diunduh, tetapi riwayat ekspor tidak dapat disimpan di browser ini.';
                historyError.hidden = false;
                showToast('File diunduh, tetapi riwayat tidak diperbarui.', 'error');
            }
        } finally {
            clearButtonLoading(confirmButton, 'Export Excel');
        }
    });

    try {
        exportHistory = readExportHistory();
    } catch (error) {
        console.error('Riwayat ekspor sebelumnya tidak dapat dibaca.', error);
        historyError.textContent = 'Riwayat ekspor sebelumnya tidak dapat dibaca dari browser ini.';
        historyError.hidden = false;
    }

    updateCustomRange();
    updateReport();
    updatePreview();
}
