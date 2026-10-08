import './bootstrap';
import Chart from 'chart.js/auto';
import './periodPicker';
import './riwayatAlert';
import './laporanPenjualan';

const theme = getComputedStyle(document.documentElement);
const colors = {
    brand: theme.getPropertyValue('--brand-orange').trim(),
    safe: theme.getPropertyValue('--safe').trim(),
    border: theme.getPropertyValue('--border-light').trim(),
    muted: theme.getPropertyValue('--text-muted').trim(),
};

Chart.defaults.font.family = theme.getPropertyValue('--font-sans').trim();
Chart.defaults.font.size = 9;
Chart.defaults.color = colors.muted;

const charts = new Map();

document.querySelectorAll('canvas[data-chart-type]').forEach((canvas) => {
    const labels = JSON.parse(canvas.dataset.chartLabels ?? '[]');
    const values = JSON.parse(canvas.dataset.chartValues ?? '[]');
    const secondary = JSON.parse(canvas.dataset.chartSecondary ?? '[]');
    const isLine = canvas.dataset.chartType === 'line';
    const datasets = [{
        data: values,
        borderColor: colors.brand,
        backgroundColor: isLine ? 'transparent' : colors.brand,
        borderWidth: 1.5,
        pointRadius: 0,
        pointHoverRadius: 2,
        tension: 0.35,
        borderRadius: 0,
        categoryPercentage: 0.62,
        barPercentage: 0.72,
    }];

    if (secondary.length > 0) {
        datasets.push({
            data: secondary,
            borderColor: colors.border,
            backgroundColor: 'transparent',
            borderWidth: 1,
            pointRadius: 0,
            tension: 0,
            borderDash: [2, 3],
        });
    }

    const chart = new Chart(canvas, {
        type: canvas.dataset.chartType,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: colors.border },
                    ticks: { color: colors.muted, maxRotation: 0 },
                },
                y: {
                    beginAtZero: !isLine,
                    grid: { color: colors.border, drawTicks: false },
                    border: { display: false },
                    ticks: { display: false, count: 4 },
                },
            },
        },
    });

    charts.set(canvas.id, chart);
});

document.querySelectorAll('[data-chart-period]').forEach((button) => {
    button.addEventListener('click', () => {
        const chartCard = button.closest('.chart-card');
        const canvas = chartCard?.querySelector('canvas');
        const chart = canvas ? charts.get(canvas.id) : null;

        if (!chart || !canvas) {
            return;
        }

        const useDailyData = button.dataset.chartPeriod === 'Hari';
        const alternateLabels = JSON.parse(canvas.dataset.chartAltLabels ?? '[]');
        const alternateValues = JSON.parse(canvas.dataset.chartAltValues ?? '[]');
        chart.data.labels = useDailyData ? alternateLabels : JSON.parse(canvas.dataset.chartLabels ?? '[]');
        chart.data.datasets[0].data = useDailyData ? alternateValues : JSON.parse(canvas.dataset.chartValues ?? '[]');
        chart.update();

        chartCard.querySelectorAll('[data-chart-period]').forEach((control) => {
            control.classList.toggle('chart-card__control--active', control === button);
        });
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'lihat' : 'sembunyi';
        button.setAttribute('aria-label', visible ? 'Lihat password' : 'Sembunyikan password');
    });
});

document.querySelectorAll('[data-reset-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        form.querySelector('[data-reset-message]').hidden = false;
    });
});

document.querySelectorAll('[data-open-slot]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelector(`#slot-dialog-${button.dataset.openSlot}`)?.showModal();
    });
});

document.querySelectorAll('[data-edit-slot]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = button.closest('form');

        form.querySelectorAll('[data-slot-view]').forEach((value) => {
            value.hidden = true;
        });
        form.querySelectorAll('.dialog__input').forEach((input) => {
            input.hidden = false;
        });
        button.hidden = true;
        form.querySelector('[data-save-slot]').hidden = false;
    });
});

document.querySelectorAll('[data-slot-dialog]').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});
