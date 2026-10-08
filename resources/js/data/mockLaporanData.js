const products = [
    { name: 'Mie Tarempa', price: 15000 },
    { name: 'Luti Gendang', price: 12000 },
    { name: 'Mie Sagu', price: 13000 },
];

const todayAt = (dayOffset, hour, minute) => {
    const date = new Date();
    date.setHours(0, 0, 0, 0);
    date.setDate(date.getDate() + dayOffset);
    date.setHours(hour, minute, 0, 0);
    return date.toISOString();
};

const makeFixedDate = (value) => new Date(value).toISOString();

const fixedAlerts = [
    {
        id: 'ALT-001',
        type: 'Temperature Warning',
        source: 'Cabinet Temperature',
        message: 'Suhu Turun ke 50',
        occurredAt: '2026-09-27T10:41:00+07:00',
        resolvedAt: '2026-09-27T11:05:00+07:00',
        priority: 'Critical',
        status: 'Resolved',
    },
    {
        id: 'ALT-002',
        type: 'Low Stock',
        source: 'Slot A2 - Mie Tarempa',
        message: 'Mie Tarempa Sisa 2 porsi',
        occurredAt: '2026-09-27T09:20:00+07:00',
        resolvedAt: null,
        priority: 'Warning',
        status: 'Unresolved',
    },
    {
        id: 'ALT-003',
        type: 'Food Holding Time',
        source: 'Slot A1 - Mie Sagu',
        message: 'Produk Lewat Batas Simpan',
        occurredAt: '2026-09-28T12:20:00+07:00',
        resolvedAt: '2026-09-28T12:40:00+07:00',
        priority: 'Warning',
        status: 'Resolved',
    },
    {
        id: 'ALT-004',
        type: 'Machine Offline',
        source: 'Vending Machine',
        message: 'VM tidak mengirim data',
        occurredAt: '2026-09-29T10:20:00+07:00',
        resolvedAt: '2026-09-29T10:30:00+07:00',
        priority: 'Critical',
        status: 'Resolved',
    },
    {
        id: 'ALT-005',
        type: 'Temperature Warning',
        source: 'Cabinet Temperature',
        message: 'Suhu Turun ke 57',
        occurredAt: '2026-09-29T11:20:00+07:00',
        resolvedAt: '2026-09-29T11:30:00+07:00',
        priority: 'Critical',
        status: 'Resolved',
    },
];

const alertKinds = [
    { type: 'Temperature Warning', source: 'Cabinet Temperature', message: 'Suhu di bawah batas normal', priority: 'Critical' },
    { type: 'Low Stock', source: 'Slot A2 - Mie Tarempa', message: 'Mie Tarempa tersisa 2 porsi', priority: 'Warning' },
    { type: 'Food Holding Time', source: 'Slot A1 - Mie Sagu', message: 'Produk melewati batas waktu simpan', priority: 'Warning' },
    { type: 'Machine Offline', source: 'Vending Machine', message: 'VM tidak mengirim data', priority: 'Critical' },
    { type: 'Temperature Warning', source: 'Cabinet Temperature', message: 'Suhu kabinet perlu diperiksa', priority: 'Critical' },
];

const generatedAlerts = Array.from({ length: 45 }, (_, index) => {
    const template = alertKinds[index % alertKinds.length];
    const occurredAt = todayAt(-((index * 9) % 395), 8 + (index % 9), (index * 7) % 60);
    const isUnresolved = index % 5 === 1;
    const resolvedAt = isUnresolved
        ? null
        : new Date(new Date(occurredAt).getTime() + (10 + (index % 35)) * 60000).toISOString();

    return {
        id: `ALT-${String(index + 6).padStart(3, '0')}`,
        ...template,
        occurredAt,
        resolvedAt,
        status: isUnresolved ? 'Unresolved' : 'Resolved',
    };
});

const transactionOffsets = [0, 0, -1, -2, -4, -6, -7, -9, -13, -16, -20, -27, -31, -38, -46, -60, -90, -120, -180, -370];

export const salesTransactions = transactionOffsets.map((offset, index) => {
    const product = products[index % products.length];
    const quantity = (index % 4) + 1;
    const time = todayAt(offset, 8 + (index % 11), (index * 13) % 60);

    return {
        id: `TRX-${String(index + 1).padStart(4, '0')}`,
        occurredAt: time,
        slot: ['A01', 'A02', 'B01', 'C01'][index % 4],
        product: product.name,
        quantity,
        unitPrice: product.price,
        total: quantity * product.price,
    };
});

export const restockRecords = [0, -2, -5, -8, -15, -24, -32, -52, -95, -185, -375].map((offset, index) => ({
    id: `RST-${String(index + 1).padStart(4, '0')}`,
    occurredAt: todayAt(offset, 7 + (index % 8), (index * 11) % 60),
    slot: ['A01', 'A02', 'B01', 'C01'][index % 4],
    product: products[index % products.length].name,
    quantity: index % 2 === 0 ? 6 : 4,
}));

export const alertRecords = [...fixedAlerts, ...generatedAlerts].map((alert) => ({
    ...alert,
    occurredAt: makeFixedDate(alert.occurredAt),
    resolvedAt: alert.resolvedAt ? makeFixedDate(alert.resolvedAt) : null,
}));
