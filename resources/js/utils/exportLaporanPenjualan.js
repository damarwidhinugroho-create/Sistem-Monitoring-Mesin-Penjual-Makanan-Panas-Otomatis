import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

const headerFill = 'FF215E98';
const zebraFill = 'FFC0E6F5';
const dateFormat = 'dd/mm/yyyy h:mm';
const currencyFormat = '"Rp"#,##0';
const workbookMime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

const asDate = (value) => value ? new Date(value) : '';

const formatRupiah = (value) => `Rp. ${new Intl.NumberFormat('id-ID').format(value)}`;

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
        if ((rowNumber - startRow) % 2 !== 0) {
            continue;
        }

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

const prepareDateColumn = (worksheet, column, firstRow, lastRow) => {
    for (let rowNumber = firstRow; rowNumber <= lastRow; rowNumber += 1) {
        worksheet.getCell(rowNumber, column).numFmt = dateFormat;
    }
};

const prepareWorkbook = ({ period, startDate, endDate, sales, restocks, alerts }) => {
    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Dashboard Operator';
    workbook.created = new Date();

    const sortedSales = [...sales].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const sortedRestocks = [...restocks].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const sortedAlerts = [...alerts].sort((left, right) => new Date(left.occurredAt) - new Date(right.occurredAt));
    const totalRevenue = sortedSales.reduce((total, sale) => total + sale.total, 0);
    const soldQuantity = sortedSales.reduce((total, sale) => total + sale.quantity, 0);
    const quantitiesByProduct = sortedSales.reduce((totals, sale) => {
        totals.set(sale.product, (totals.get(sale.product) ?? 0) + sale.quantity);
        return totals;
    }, new Map());
    const bestSeller = [...quantitiesByProduct.entries()]
        .sort((left, right) => right[1] - left[1])[0]?.[0] ?? '—';
    const summary = workbook.addWorksheet('Laporan Operasional');

    summary.columns = [
        { width: 25 }, { width: 22 }, { width: 22 }, { width: 22 },
        { width: 22 }, { width: 22 }, { width: 19 }, { width: 24 },
    ];
    summary.mergeCells('A1:H1');
    summary.getCell('A1').value = 'LAPORAN PENJUALAN OPERASIONAL';
    summary.getCell('A1').font = { bold: true, size: 16, color: { argb: headerFill } };
    summary.getCell('A1').alignment = { vertical: 'middle', horizontal: 'left' };
    summary.getRow(1).height = 30;

    summary.getRow(2).values = [
        'Filter Periode', period, 'Tanggal Mulai', startDate, 'Tanggal Akhir', endDate,
    ];
    summary.getRow(2).font = { bold: true };
    summary.getCell('D2').numFmt = 'dd/mm/yyyy';
    summary.getCell('F2').numFmt = 'dd/mm/yyyy';

    const cards = [
        { label: 'TOTAL TRANSAKSI', value: sortedSales.length, firstColumn: 1 },
        { label: 'PENDAPATAN', value: formatRupiah(totalRevenue), firstColumn: 3 },
        { label: 'PRODUK TERJUAL', value: soldQuantity, firstColumn: 5 },
        { label: 'AKTIVITAS RESTOK', value: sortedRestocks.length, firstColumn: 7 },
    ];

    cards.forEach(({ label, value, firstColumn }) => {
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
        record.id, asDate(record.occurredAt), record.slot, record.product, record.quantity,
    ]));
    prepareDateColumn(summary, 2, restockTitleRow + 2, restockEndRow - 1);

    const alertTitleRow = restockEndRow + 2;
    summary.mergeCells(`A${alertTitleRow}:H${alertTitleRow}`);
    summary.getCell(`A${alertTitleRow}`).value = 'RINGKASAN ALERT';
    applyHeaderStyle(summary.getRow(alertTitleRow));
    const alertEndRow = addTable(summary, alertTitleRow + 1, [
        'ID Alert', 'Tanggal/Waktu', 'Jenis Alert', 'Sumber', 'Pesan', 'Prioritas', 'Status', 'Waktu Selesai',
    ], sortedAlerts.map((alert) => [
        alert.id, asDate(alert.occurredAt), alert.type, alert.source, alert.message,
        alert.priority, alert.status, asDate(alert.resolvedAt),
    ]));
    prepareDateColumn(summary, 2, alertTitleRow + 2, alertEndRow - 1);
    prepareDateColumn(summary, 8, alertTitleRow + 2, alertEndRow - 1);

    const salesSheet = workbook.addWorksheet('Data Penjualan');
    salesSheet.columns = [
        { header: 'ID Transaksi', key: 'id', width: 17 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Slot', key: 'slot', width: 12 },
        { header: 'Produk', key: 'product', width: 22 },
        { header: 'Jumlah', key: 'quantity', width: 12 },
        { header: 'Harga Satuan', key: 'unitPrice', width: 18 },
        { header: 'Total', key: 'total', width: 18 },
    ];
    salesSheet.addRows(sortedSales.map((sale) => ({
        ...sale,
        occurredAt: asDate(sale.occurredAt),
    })));

    const restockSheet = workbook.addWorksheet('Data Restock');
    restockSheet.columns = [
        { header: 'ID Restok', key: 'id', width: 17 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Slot', key: 'slot', width: 12 },
        { header: 'Produk', key: 'product', width: 22 },
        { header: 'Jumlah Ditambahkan', key: 'quantity', width: 22 },
    ];
    restockSheet.addRows(sortedRestocks.map((record) => ({
        ...record,
        occurredAt: asDate(record.occurredAt),
    })));

    const alertsSheet = workbook.addWorksheet('Data Alert');
    alertsSheet.columns = [
        { header: 'ID Alert', key: 'id', width: 17 },
        { header: 'Tanggal/Waktu', key: 'occurredAt', width: 22 },
        { header: 'Jenis Alert', key: 'type', width: 25 },
        { header: 'Sumber', key: 'source', width: 32 },
        { header: 'Pesan', key: 'message', width: 36 },
        { header: 'Prioritas', key: 'priority', width: 14 },
        { header: 'Status', key: 'status', width: 16 },
        { header: 'Waktu Selesai', key: 'resolvedAt', width: 22 },
    ];
    alertsSheet.addRows(sortedAlerts.map((alert) => ({
        id: alert.id,
        occurredAt: asDate(alert.occurredAt),
        type: alert.type,
        source: alert.source,
        message: alert.message,
        priority: alert.priority,
        status: alert.status,
        resolvedAt: asDate(alert.resolvedAt),
    })));

    [
        { sheet: salesSheet, rows: sortedSales.length, dateColumns: [2], currencyColumns: [6, 7] },
        { sheet: restockSheet, rows: sortedRestocks.length, dateColumns: [2], currencyColumns: [] },
        { sheet: alertsSheet, rows: sortedAlerts.length, dateColumns: [2, 8], currencyColumns: [] },
    ].forEach(({ sheet, rows, dateColumns, currencyColumns }) => {
        applyHeaderStyle(sheet.getRow(1));
        applyZebraRows(sheet, 2, rows + 1, sheet.columnCount);
        dateColumns.forEach((column) => prepareDateColumn(sheet, column, 2, rows + 1));
        currencyColumns.forEach((column) => {
            for (let rowNumber = 2; rowNumber <= rows + 1; rowNumber += 1) {
                sheet.getCell(rowNumber, column).numFmt = currencyFormat;
            }
        });
        sheet.views = [{ state: 'frozen', ySplit: 1 }];
    });

    return workbook;
};

export const exportLaporanPenjualan = async (reportData) => {
    const workbook = prepareWorkbook(reportData);
    const content = await workbook.xlsx.writeBuffer();
    const blob = new Blob([content], { type: workbookMime });
    const start = reportData.startDate.toISOString().slice(0, 10);
    const end = reportData.endDate.toISOString().slice(0, 10);

    saveAs(blob, `Laporan_Penjualan_Operasional_${start}_sd_${end}.xlsx`);
};

export const buildLaporanPenjualanWorkbook = prepareWorkbook;
