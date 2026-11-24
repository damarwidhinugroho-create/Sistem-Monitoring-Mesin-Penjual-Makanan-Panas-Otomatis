import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

const headerFill = 'FF215E98';
const zebraFill = 'FFC0E6F5';
const dateFormat = 'dd/mm/yyyy hh:mm';
const dateOnlyFormat = 'dd/mm/yyyy';
const currencyFormat = '"Rp. "#,##0';
const workbookMime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

const safeValue = (value) => value === null || value === undefined ? '' : value;

const wibDateParts = (value) => {
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return null;

    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(date);
    return Object.fromEntries(parts.map(({ type, value: partValue }) => [type, Number(partValue)]));
};

const asWibExcelDate = (value) => {
    if (!value) return '';
    const parts = wibDateParts(value);
    if (!parts) return '';
    return new Date(Date.UTC(
        parts.year,
        parts.month - 1,
        parts.day,
        parts.hour,
        parts.minute,
        parts.second,
    ));
};

const asWibExcelDateOnly = (value) => {
    if (!value) return '';
    const parts = wibDateParts(value);
    if (!parts) return '';
    return new Date(Date.UTC(parts.year, parts.month - 1, parts.day));
};

const formatWibDateKey = (value) => {
    const parts = wibDateParts(value);
    if (!parts) return '';
    return `${parts.year}-${String(parts.month).padStart(2, '0')}-${String(parts.day).padStart(2, '0')}`;
};

const applyHeaderStyle = (row) => {
    row.eachCell((cell) => {
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: headerFill } };
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        cell.alignment = { vertical: 'middle', horizontal: 'left', wrapText: true };
    });
    row.height = 30;
};

const applyZebraRows = (worksheet, startRow, endRow, columnCount) => {
    for (let rowNumber = startRow; rowNumber <= endRow; rowNumber += 1) {
        if ((rowNumber - startRow) % 2 !== 0) continue;

        for (let columnNumber = 1; columnNumber <= columnCount; columnNumber += 1) {
            worksheet.getCell(rowNumber, columnNumber).fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: zebraFill },
            };
        }
    }
};

const addTable = (worksheet, startRow, headers, records) => {
    const headerRow = worksheet.getRow(startRow);
    headerRow.values = headers;
    applyHeaderStyle(headerRow);

    records.forEach((record, index) => {
        worksheet.getRow(startRow + index + 1).values = record;
    });
    applyZebraRows(worksheet, startRow + 1, startRow + records.length, headers.length);
    return startRow + records.length + 1;
};

const setDateFormat = (worksheet, column, firstRow, lastRow) => {
    for (let rowNumber = firstRow; rowNumber <= lastRow; rowNumber += 1) {
        worksheet.getCell(rowNumber, column).numFmt = dateFormat;
    }
};

const setCurrencyFormat = (worksheet, columns, firstRow, lastRow) => {
    for (const column of columns) {
        for (let rowNumber = firstRow; rowNumber <= lastRow; rowNumber += 1) {
            worksheet.getCell(rowNumber, column).numFmt = currencyFormat;
        }
    }
};

const findBestSeller = (sales) => {
    const quantities = sales.reduce((totals, sale) => {
        if (typeof sale.product !== 'string' || sale.product.trim() === '') return totals;
        totals.set(sale.product, (totals.get(sale.product) ?? 0) + (Number(sale.quantity) || 0));
        return totals;
    }, new Map());

    // Ties are resolved alphabetically so repeated exports produce the same result.
    return [...quantities.entries()]
        .sort(([productA, quantityA], [productB, quantityB]) => quantityB - quantityA || productA.localeCompare(productB, 'id'))
        [0]?.[0] ?? '—';
};

const prepareSalesSheet = (workbook, sales) => {
    const sheet = workbook.addWorksheet('Data Penjualan');
    sheet.columns = [
        { header: 'ID Transaksi', key: 'id', width: 18 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Slot', key: 'slot', width: 12 },
        { header: 'Produk', key: 'product', width: 24 },
        { header: 'Jumlah', key: 'quantity', width: 12 },
        { header: 'Harga Satuan', key: 'unitPrice', width: 18 },
        { header: 'Total', key: 'total', width: 18 },
    ];
    sheet.addRows(sales.map((sale) => ({
        id: safeValue(sale.id),
        occurredAt: asWibExcelDate(sale.occurredAt),
        slot: safeValue(sale.slot),
        product: safeValue(sale.product),
        quantity: sale.quantity === null || sale.quantity === undefined ? '' : Number(sale.quantity),
        unitPrice: sale.unitPrice === null || sale.unitPrice === undefined ? '' : Number(sale.unitPrice),
        total: sale.total === null || sale.total === undefined ? '' : Number(sale.total),
    })));
    return { sheet, rows: sales.length, dateColumns: [2], currencyColumns: [6, 7] };
};

const prepareRestockSheet = (workbook, restocks) => {
    const sheet = workbook.addWorksheet('Data Restock');
    sheet.columns = [
        { header: 'ID Restok', key: 'id', width: 18 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Slot', key: 'slot', width: 12 },
        { header: 'Produk', key: 'product', width: 24 },
        { header: 'Jumlah Ditambahkan', key: 'quantity', width: 22 },
    ];
    sheet.addRows(restocks.map((record) => ({
        id: safeValue(record.id),
        occurredAt: asWibExcelDate(record.occurredAt),
        slot: safeValue(record.slot),
        product: safeValue(record.product),
        quantity: record.quantity === null || record.quantity === undefined ? '' : Number(record.quantity),
    })));
    return { sheet, rows: restocks.length, dateColumns: [2], currencyColumns: [] };
};

const prepareAlertSheet = (workbook, alerts) => {
    const sheet = workbook.addWorksheet('Data Alert');
    sheet.columns = [
        { header: 'ID Alert', key: 'id', width: 18 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Jenis Alert', key: 'type', width: 25 },
        { header: 'Sumber', key: 'source', width: 32 },
        { header: 'Pesan', key: 'message', width: 38 },
        { header: 'Prioritas', key: 'priority', width: 15 },
        { header: 'Status', key: 'status', width: 16 },
        { header: 'Waktu Selesai', key: 'resolvedAt', width: 22 },
    ];
    sheet.addRows(alerts.map((alert) => ({
        id: safeValue(alert.id),
        occurredAt: asWibExcelDate(alert.occurredAt),
        type: safeValue(alert.type),
        source: safeValue(alert.source),
        message: safeValue(alert.message),
        priority: safeValue(alert.priority),
        status: safeValue(alert.status),
        resolvedAt: asWibExcelDate(alert.resolvedAt),
    })));
    return { sheet, rows: alerts.length, dateColumns: [2, 8], currencyColumns: [] };
};

export const buildSalesReportWorkbook = ({
    period,
    startDate,
    endDate,
    sales = [],
    restocks = [],
    alerts = [],
}) => {
    const sortedSales = [...sales].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const sortedRestocks = [...restocks].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const sortedAlerts = [...alerts].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const totalRevenue = sortedSales.reduce((sum, sale) => sum + (Number(sale.total) || 0), 0);
    const soldQuantity = sortedSales.reduce((sum, sale) => sum + (Number(sale.quantity) || 0), 0);
    const bestSeller = findBestSeller(sortedSales);
    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Dashboard Operator';
    workbook.created = new Date();

    const summary = workbook.addWorksheet('Laporan Operasional');
    summary.columns = [
        { width: 25 }, { width: 22 }, { width: 22 }, { width: 22 },
        { width: 22 }, { width: 22 }, { width: 22 }, { width: 24 },
    ];
    summary.mergeCells('A1:H1');
    summary.getCell('A1').value = 'LAPORAN PENJUALAN OPERASIONAL';
    summary.getCell('A1').font = { bold: true, size: 16, color: { argb: headerFill } };
    summary.getCell('A1').alignment = { vertical: 'middle', horizontal: 'center' };
    summary.getRow(1).height = 30;

    summary.getRow(2).values = [
        'Filter Periode', safeValue(period), 'Tanggal Mulai', asWibExcelDateOnly(startDate),
        'Tanggal Akhir', asWibExcelDateOnly(endDate),
    ];
    summary.getRow(2).font = { bold: true };
    summary.getCell('D2').numFmt = dateOnlyFormat;
    summary.getCell('F2').numFmt = dateOnlyFormat;

    const cards = [
        { label: 'TOTAL TRANSAKSI', value: sortedSales.length, firstColumn: 1 },
        { label: 'PENDAPATAN', value: totalRevenue, firstColumn: 3, currency: true },
        { label: 'PRODUK TERJUAL', value: soldQuantity, firstColumn: 5 },
        { label: 'AKTIVITAS RESTOK', value: sortedRestocks.length, firstColumn: 7 },
    ];
    cards.forEach(({ label, value, firstColumn, currency }) => {
        const firstLetter = String.fromCharCode(64 + firstColumn);
        const lastLetter = String.fromCharCode(65 + firstColumn);
        summary.mergeCells(`${firstLetter}4:${lastLetter}4`);
        summary.mergeCells(`${firstLetter}5:${lastLetter}5`);
        const labelCell = summary.getCell(`${firstLetter}4`);
        labelCell.value = label;
        labelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: headerFill } };
        labelCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        labelCell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
        const valueCell = summary.getCell(`${firstLetter}5`);
        valueCell.value = value;
        valueCell.font = { bold: true, size: 14 };
        valueCell.alignment = { horizontal: 'center', vertical: 'middle' };
        valueCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: zebraFill } };
        if (currency) valueCell.numFmt = currencyFormat;
    });

    addTable(summary, 8, ['JENIS', 'NILAI'], [
        ['Jumlah Transaksi', sortedSales.length],
        ['Produk Terjual', soldQuantity],
        ['Total Pendapatan', totalRevenue],
        ['Produk Terlaris', bestSeller],
    ]);
    summary.getCell('B11').numFmt = currencyFormat;

    const restockTitleRow = 15;
    summary.mergeCells(`A${restockTitleRow}:E${restockTitleRow}`);
    summary.getCell(`A${restockTitleRow}`).value = 'RINGKASAN RESTOCK';
    applyHeaderStyle(summary.getRow(restockTitleRow));
    const restockEndRow = addTable(summary, restockTitleRow + 1, [
        'ID Restock', 'Tanggal/Waktu', 'Slot', 'Produk', 'Jumlah Ditambahkan',
    ], sortedRestocks.map((record) => [
        safeValue(record.id), asWibExcelDate(record.occurredAt), safeValue(record.slot),
        safeValue(record.product), record.quantity === null || record.quantity === undefined ? '' : Number(record.quantity),
    ]));
    setDateFormat(summary, 2, restockTitleRow + 2, restockEndRow - 1);

    const alertTitleRow = restockEndRow + 2;
    summary.mergeCells(`A${alertTitleRow}:H${alertTitleRow}`);
    summary.getCell(`A${alertTitleRow}`).value = 'RINGKASAN ALERT';
    applyHeaderStyle(summary.getRow(alertTitleRow));
    const alertEndRow = addTable(summary, alertTitleRow + 1, [
        'ID Alert', 'Tanggal/Waktu', 'Jenis Alert', 'Sumber', 'Pesan', 'Prioritas', 'Status', 'Waktu Selesai',
    ], sortedAlerts.map((alert) => [
        safeValue(alert.id), asWibExcelDate(alert.occurredAt), safeValue(alert.type), safeValue(alert.source),
        safeValue(alert.message), safeValue(alert.priority), safeValue(alert.status), asWibExcelDate(alert.resolvedAt),
    ]));
    setDateFormat(summary, 2, alertTitleRow + 2, alertEndRow - 1);
    setDateFormat(summary, 8, alertTitleRow + 2, alertEndRow - 1);

    const footerRow = alertEndRow + 2;
    summary.mergeCells(`A${footerRow}:H${footerRow}`);
    summary.getCell(`A${footerRow}`).value = 'Laporan dibuat oleh Dashboard Operator. Waktu pada laporan menggunakan zona WIB (UTC+7).';
    summary.getCell(`A${footerRow}`).font = { italic: true, color: { argb: 'FF53665D' } };
    summary.getCell(`A${footerRow}`).alignment = { horizontal: 'left', vertical: 'middle', wrapText: true };

    const dataSheets = [
        prepareSalesSheet(workbook, sortedSales),
        prepareRestockSheet(workbook, sortedRestocks),
        prepareAlertSheet(workbook, sortedAlerts),
    ];
    dataSheets.forEach(({ sheet, rows, dateColumns, currencyColumns }) => {
        applyHeaderStyle(sheet.getRow(1));
        applyZebraRows(sheet, 2, rows + 1, sheet.columnCount);
        dateColumns.forEach((column) => setDateFormat(sheet, column, 2, rows + 1));
        setCurrencyFormat(sheet, currencyColumns, 2, rows + 1);
        sheet.views = [{ state: 'frozen', ySplit: 1 }];
        sheet.autoFilter = { from: 'A1', to: `${sheet.getColumn(sheet.columnCount).letter}1` };
    });

    return workbook;
};

export const exportLaporanPenjualan = async (reportData) => {
    const workbook = buildSalesReportWorkbook(reportData);
    const content = await workbook.xlsx.writeBuffer();
    const blob = new Blob([content], { type: workbookMime });
    const start = reportData.startDateKey ?? formatWibDateKey(reportData.startDate);
    const end = reportData.endDateKey ?? formatWibDateKey(reportData.endDate);
    saveAs(blob, `Laporan_Penjualan_Operasional_${start}_sd_${end}.xlsx`);
};

export const buildLaporanPenjualanWorkbook = buildSalesReportWorkbook;
