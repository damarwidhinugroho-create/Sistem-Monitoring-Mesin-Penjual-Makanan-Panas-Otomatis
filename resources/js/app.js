import './bootstrap';
import Chart from 'chart.js/auto';
import './periodPicker';
import './riwayatAlert';
import './laporanPenjualan';

const theme = getComputedStyle(document.documentElement);
const colors = {
    brand: theme.getPropertyValue('--chart-primary').trim(),
    safe: theme.getPropertyValue('--safe').trim(),
    border: theme.getPropertyValue('--border-light').trim(),
    secondary: theme.getPropertyValue('--chart-secondary').trim(),
    muted: theme.getPropertyValue('--text-muted').trim(),
};

Chart.defaults.font.family = theme.getPropertyValue('--font-sans').trim();
Chart.defaults.font.size = 11;
Chart.defaults.color = colors.muted;

const charts = new Map();
let themeTransitionTimer;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const progressiveLineAnimation = {
    x: {
        type: 'number',
        easing: 'linear',
        duration: reduceMotion ? 0 : 900,
        from: Number.NaN,
        delay(context) {
            return !reduceMotion && context.type === 'data' ? context.index * 110 : 0;
        },
    },
    y: {
        type: 'number',
        easing: 'easeOutQuart',
        duration: reduceMotion ? 0 : 700,
        delay(context) {
            return !reduceMotion && context.type === 'data' ? context.index * 110 : 0;
        },
    },
};

function updateTheme(animateCharts = false) {
    const isDark = document.documentElement.dataset.theme === 'dark';
    const theme = getComputedStyle(document.documentElement);

    colors.brand = theme.getPropertyValue('--chart-primary').trim();
    colors.border = theme.getPropertyValue('--border-light').trim();
    colors.secondary = theme.getPropertyValue('--chart-secondary').trim();
    colors.muted = theme.getPropertyValue('--text-muted').trim();
    Chart.defaults.color = colors.muted;

    charts.forEach((chart) => {
        chart.data.datasets.forEach((dataset, index) => {
            dataset.borderColor = index === 0 ? colors.brand : colors.secondary;
            dataset.backgroundColor = index === 0 && chart.config.type !== 'line'
                ? colors.brand
                : 'transparent';
        });

        chart.options.scales.x.border.color = colors.border;
        chart.options.scales.x.ticks.color = colors.muted;
        chart.options.scales.y.grid.color = colors.border;
        if (animateCharts) {
            chart.options.animation = { duration: 520, easing: 'easeInOutCubic' };
            chart.update();
        }
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        button.setAttribute('title', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
    });
}

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
            borderColor: colors.secondary,
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
            animation: reduceMotion ? false : {
                duration: 850,
                easing: 'easeOutQuart',
            },
            animations: isLine ? progressiveLineAnimation : undefined,
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

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const isDark = document.documentElement.dataset.theme !== 'dark';
        const root = document.documentElement;
        const transitionDuration = reduceMotion ? 0 : 720;

        if (transitionDuration > 0) {
            root.dataset.themeChanging = '';
        }
        window.clearTimeout(themeTransitionTimer);

        if (isDark) {
            root.dataset.theme = 'dark';
        } else {
            delete root.dataset.theme;
        }
        root.style.backgroundColor = isDark ? '#111827' : '#F3F4F6';

        localStorage.setItem('operator-theme', isDark ? 'dark' : 'light');
        updateTheme(transitionDuration > 0);
        themeTransitionTimer = window.setTimeout(() => {
            delete root.dataset.themeChanging;
        }, transitionDuration);
    });
});

updateTheme();

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
