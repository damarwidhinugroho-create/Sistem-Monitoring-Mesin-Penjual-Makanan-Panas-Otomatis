import './bootstrap';
import Chart from 'chart.js/auto';
import './periodPicker';
import './pageNavigation';
import './dashboardSkeleton';
import './riwayatAlert';
import './laporanPenjualan';
import './inventorySlots';
import './inventoryTables';
import './interactions';

const theme = getComputedStyle(document.documentElement);
const colors = {
    brand: theme.getPropertyValue('--chart-primary').trim(),
    bar: theme.getPropertyValue('--chart-bar').trim(),
    safe: theme.getPropertyValue('--safe').trim(),
    border: theme.getPropertyValue('--border-light').trim(),
    secondary: theme.getPropertyValue('--chart-secondary').trim(),
    muted: theme.getPropertyValue('--text-muted').trim(),
};

Chart.defaults.font.family = theme.getPropertyValue('--font-sans').trim();
Chart.defaults.font.size = 11;
Chart.defaults.color = colors.muted;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
Chart.defaults.animation = reduceMotion
    ? false
    : {
        duration: 650,
        easing: 'easeOutQuart',
    };

const charts = new Map();

function getBarBackground(data, highlightLatest) {
    if (!highlightLatest) {
        return colors.bar;
    }

    return data.map((_, index) => (
        index === data.length - 1 ? colors.brand : colors.bar
    ));
}

function formatCurrency(value) {
    return `Rp${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value)}`;
}

function updateTheme() {
    const isDark = document.documentElement.dataset.theme === 'dark';
    const theme = getComputedStyle(document.documentElement);

    colors.brand = theme.getPropertyValue('--chart-primary').trim();
    colors.bar = theme.getPropertyValue('--chart-bar').trim();
    colors.border = theme.getPropertyValue('--border-light').trim();
    colors.secondary = theme.getPropertyValue('--chart-secondary').trim();
    colors.muted = theme.getPropertyValue('--text-muted').trim();
    Chart.defaults.color = colors.muted;

    charts.forEach((chart) => {
        chart.data.datasets.forEach((dataset, index) => {
            const isBarChart = chart.config.type !== 'line';
            dataset.borderColor = index === 0
                ? (isBarChart ? colors.bar : colors.brand)
                : colors.secondary;
            dataset.backgroundColor = index === 0 && isBarChart
                ? getBarBackground(dataset.data, chart.canvas.dataset.chartHighlightLatest === 'true')
                : 'transparent';
        });

        chart.options.scales.x.border.color = colors.border;
        chart.options.scales.x.ticks.color = colors.muted;
        chart.options.scales.y.grid.color = colors.border;
        chart.update(reduceMotion ? 'none' : undefined);
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
    const highlightLatest = canvas.dataset.chartHighlightLatest === 'true';
    const primaryChartColor = isLine ? colors.brand : colors.bar;
    const datasets = [{
        data: values,
        borderColor: primaryChartColor,
        backgroundColor: isLine ? 'transparent' : getBarBackground(values, highlightLatest),
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
            animation: reduceMotion
                ? false
                : {
                    duration: 650,
                    easing: 'easeOutQuart',
                },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(28, 41, 33, 0.92)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    borderColor: 'rgba(255, 255, 255, 0.14)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: canvas.dataset.chartValueFormat === 'currency'
                        ? {
                            label: (context) => formatCurrency(context.parsed.y),
                        }
                        : undefined,
                    animation: reduceMotion ? false : { duration: 180 },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: colors.border },
                    ticks: { color: colors.muted, maxRotation: 0, font: { size: 12 } },
                },
                y: {
                    beginAtZero: !isLine,
                    grid: { color: colors.border, drawTicks: false },
                    border: { display: false },
                    ticks: { display: true, count: 4, color: colors.muted, font: { size: 12 } },
                },
            },
        },
    });

    charts.set(canvas.id, chart);
    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => chart.resize());
        observer.observe(canvas.closest('.chart-card__plot') ?? canvas.parentElement);
    }
});

    const themeSwitches = document.querySelectorAll('.sidebar__theme');
    let themeTransitionTimer;

    function applyTheme(theme) {
        const root = document.documentElement;
        const nextTheme = theme === 'dark' ? 'dark' : 'light';

        if (nextTheme === 'dark') {
            root.dataset.theme = 'dark';
        } else {
            delete root.dataset.theme;
        }
        root.style.colorScheme = nextTheme;

        localStorage.setItem('operator-theme', nextTheme);
        root.dataset.themeChanging = '';
        window.clearTimeout(themeTransitionTimer);

        themeSwitches.forEach((switcher) => {
            switcher.dataset.active = nextTheme;
            switcher.classList.toggle('is-dark', nextTheme === 'dark');
            switcher.querySelectorAll('[data-theme-choice]').forEach((button) => {
                const active = button.dataset.themeChoice === nextTheme;
                button.setAttribute('aria-checked', String(active));
                button.tabIndex = active ? 0 : -1;
            });
        });
        updateTheme();

        themeTransitionTimer = window.setTimeout(() => {
            delete root.dataset.themeChanging;
        }, reduceMotion ? 0 : 180);
    }

    themeSwitches.forEach((switcher) => {
        switcher.querySelectorAll('[data-theme-choice]').forEach((button) => {
            button.addEventListener('click', () => applyTheme(button.dataset.themeChoice));
        });

        switcher.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            switcher.querySelector(`[data-theme-choice="${nextTheme}"]`)?.focus();
        });
    });

    applyTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
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
        chart.update(reduceMotion ? 'none' : undefined);

        chartCard.querySelectorAll('[data-chart-period]').forEach((control) => {
            control.classList.toggle('chart-card__control--active', control === button);
        });
    });
});

document.querySelectorAll('[data-dashboard-chart-period]').forEach((select) => {
    select.addEventListener('change', () => {
        const chart = charts.get(select.dataset.chartTarget);
        const selectedOption = select.selectedOptions[0];
        const panel = select.closest('.dashboard__panel');

        if (!chart || !selectedOption || !panel) {
            return;
        }

        const canvas = chart.canvas;
        const useWeeklyData = select.value === 'week';
        const labels = JSON.parse(useWeeklyData
            ? canvas.dataset.chartAltLabels ?? '[]'
            : canvas.dataset.chartLabels ?? '[]');
        const values = JSON.parse(useWeeklyData
            ? canvas.dataset.chartAltValues ?? '[]'
            : canvas.dataset.chartValues ?? '[]');

        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.data.datasets[0].backgroundColor = getBarBackground(
            values,
            canvas.dataset.chartHighlightLatest === 'true',
        );
        chart.update(reduceMotion ? 'none' : undefined);

        panel.querySelector('[data-dashboard-sales-total]').textContent = selectedOption.dataset.total;
        panel.querySelector('[data-dashboard-sales-change]').textContent = selectedOption.dataset.change;
        panel.querySelector('[data-dashboard-sales-comparison]').textContent = selectedOption.dataset.comparison;
        canvas.setAttribute(
            'aria-label',
            useWeeklyData ? 'Grafik penjualan simulasi 7 hari terakhir' : 'Grafik penjualan simulasi per jam',
        );
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) {
            return;
        }
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.classList.add('is-changing');
        window.setTimeout(() => {
            button.textContent = visible ? 'LIHAT' : 'HIDE';
            button.classList.remove('is-changing');
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 90);
        button.setAttribute('aria-label', visible ? 'Lihat password' : 'Sembunyikan password');
        button.setAttribute('aria-pressed', String(!visible));
        input.focus();
    });
});

const slotDialog = document.querySelector('[data-slot-dialog]');

if (slotDialog) {
    const slotForm = slotDialog.querySelector('[data-slot-form]');
    const slotTitle = slotDialog.querySelector('[data-slot-dialog-title]');
    const slotId = slotDialog.querySelector('[data-slot-id]');
    const slotProduct = slotDialog.querySelector('[data-slot-product]');
    const slotPrice = slotDialog.querySelector('[data-slot-price]');
    const stockInput = slotDialog.querySelector('[name="stock"]');
    const filledAtInput = slotDialog.querySelector('[name="filled_at"]');

    document.querySelectorAll('[data-open-slot]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!slotForm || !slotTitle || !slotId || !slotProduct || !slotPrice || !stockInput || !filledAtInput) {
                return;
            }

            slotForm.action = button.dataset.updateUrl;
            slotTitle.textContent = 'Edit Stok';
            slotId.textContent = button.dataset.openSlot;
            slotProduct.textContent = button.dataset.slotProduct;
            slotPrice.textContent = button.dataset.slotPrice;
            stockInput.value = button.dataset.slotStock;
            stockInput.max = button.dataset.slotCapacity;
            filledAtInput.value = button.dataset.filledAt;
            slotDialog.showModal();
        });
    });

    slotDialog.querySelectorAll('[data-close-slot]').forEach((button) => {
        button.addEventListener('click', () => slotDialog.close());
    });

    slotDialog.addEventListener('click', (event) => {
        if (event.target === slotDialog) {
            slotDialog.close();
        }
    });
}
