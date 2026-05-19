/**
 * Reads a CSS custom property from :root so charts pick up the live theme
 * without hard-coding hex values. Falls back to a safe default if the var
 * isn't defined (e.g. in a JSDOM test environment).
 */
function cssVar(name, fallback) {
    const value = getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();
    return value || fallback;
}

/**
 * Lazy-load Chart.js so a missing or not-yet-prebundled dependency doesn't
 * break the entire app.js bundle (which would, in turn, prevent dashy-ui.js
 * from registering its Alpine stores). Cached after first resolution.
 */
let chartPromise = null;
function loadChart() {
    if (! chartPromise) {
        chartPromise = import('chart.js/auto')
            .then((m) => m.default)
            .catch((err) => {
                chartPromise = null;
                console.error('[dashy-charts] failed to load chart.js', err);
                throw err;
            });
    }
    return chartPromise;
}

/**
 * Chart instances live OUTSIDE Alpine's reactive scope. Chart.js objects
 * carry circular internal refs that explode Alpine's reactivity proxy
 * (Maximum call stack / Cannot set 'fullSize'). Keyed by canvas so the
 * Alpine wrapper only ever holds a DOM node.
 */
const chartByCanvas = new WeakMap();
const pendingByCanvas = new WeakMap();

/**
 * Format an `hours` float (e.g. 1.5) as a compact "1h 30m" string. Used in
 * the chart tooltip and kept colocated with the chart wrapper so other
 * places can adopt the same formatting without re-implementing it.
 */
function formatHoursAsHm(hours) {
    const safe = Number(hours) || 0;
    if (safe <= 0) return '0m';
    const whole = Math.floor(safe);
    const minutes = Math.round((safe - whole) * 60);
    if (whole > 0 && minutes > 0) return `${whole}h ${minutes}m`;
    if (whole > 0) return `${whole}h`;
    return `${minutes}m`;
}

/**
 * Format a money amount with apostrophe thousands separator and two decimals,
 * matching the PHP-side number_format($amount, 2, '.', "'") used elsewhere on
 * the dashboard so the tooltip and the totals card render identically.
 */
function formatMoney(amount) {
    const safe = Number(amount) || 0;
    const parts = safe.toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, "'");
    return parts.join('.');
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('dashyHoursChart', (labels = [], values = [], counts = [], billingRate = null) => ({
        canvasEl: null,

        init() {
            const canvas = this.$el;
            if (! (canvas instanceof Element)) return;

            this.canvasEl = canvas;
            pendingByCanvas.set(canvas, {
                labels: [...labels],
                values: [...values],
                counts: [...counts],
            });

            const blue = cssVar('--blue', '#5992c6');
            const blueHover = cssVar('--blue-deep', '#0a2a92');
            const muted = cssVar('--ink-muted', '#a89c89');
            const grid = cssVar('--border', 'rgba(255,255,255,0.06)');
            const surface = cssVar('--surface', '#ffffff');
            const ink = cssVar('--ink', '#1a1a1a');
            const inkDim = cssVar('--ink-dim', '#9a9a9a');

            loadChart().then((Chart) => {
                if (! canvas.isConnected) return;
                if (chartByCanvas.has(canvas)) return;

                const initial = pendingByCanvas.get(canvas) ?? { labels: [], values: [], counts: [] };

                const chart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: initial.labels,
                        datasets: [
                            {
                                data: initial.values,
                                // Entry counts ride along on the dataset so the tooltip
                                // callback can read them via ctx.dataset.entries[ctx.dataIndex].
                                entries: initial.counts,
                                backgroundColor: blue,
                                hoverBackgroundColor: blueHover,
                                borderRadius: 6,
                                borderSkipped: false,
                                maxBarThickness: 28,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 250 },
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: muted,
                                    precision: 0,
                                    callback: (v) => `${v}h`,
                                },
                                grid: { color: grid },
                            },
                            x: {
                                ticks: { color: muted },
                                grid: { display: false },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                backgroundColor: surface,
                                titleColor: ink,
                                bodyColor: ink,
                                borderColor: grid,
                                borderWidth: 1,
                                padding: { top: 10, right: 12, bottom: 10, left: 12 },
                                cornerRadius: 10,
                                displayColors: false,
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12.5, weight: '500' },
                                bodySpacing: 4,
                                caretSize: 5,
                                titleMarginBottom: 6,
                                callbacks: {
                                    title: (items) => {
                                        const item = items[0];
                                        if (! item) return '';
                                        // Chart x labels are bare day numbers (e.g. "18").
                                        return `Day ${item.label}`;
                                    },
                                    label: (ctx) => {
                                        const hours = Number(ctx.parsed.y) || 0;
                                        const entries = Number(ctx.dataset.entries?.[ctx.dataIndex]) || 0;
                                        const entryLabel = entries === 1 ? '1 entry' : `${entries} entries`;
                                        const lines = [
                                            `Total · ${formatHoursAsHm(hours)}`,
                                            entryLabel,
                                        ];
                                        if (billingRate && billingRate.rate) {
                                            const money = hours * Number(billingRate.rate);
                                            lines.push(`${formatMoney(money)} ${billingRate.currency}`);
                                        }
                                        return lines;
                                    },
                                    labelTextColor: (ctx) => {
                                        // Make the second line (entry count) feel like a sub-label.
                                        return ctx.dataIndex === 0 ? ink : inkDim;
                                    },
                                },
                            },
                        },
                    },
                });

                chartByCanvas.set(canvas, chart);
                pendingByCanvas.delete(canvas);
            }).catch(() => {
                /* error already logged in loadChart */
            });
        },

        update(nextLabels, nextValues, nextCounts) {
            const canvas = this.canvasEl;
            if (! canvas) return;

            const labels = [...(nextLabels ?? [])];
            const values = [...(nextValues ?? [])];
            const counts = [...(nextCounts ?? [])];

            const chart = chartByCanvas.get(canvas);
            if (! chart) {
                pendingByCanvas.set(canvas, { labels, values, counts });
                return;
            }

            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].entries = counts;
            chart.update();
        },

        destroy() {
            const canvas = this.canvasEl;
            if (! canvas) return;
            const chart = chartByCanvas.get(canvas);
            chart?.destroy();
            chartByCanvas.delete(canvas);
            pendingByCanvas.delete(canvas);
        },
    }));
});
