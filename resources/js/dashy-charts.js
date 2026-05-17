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

document.addEventListener('alpine:init', () => {
    window.Alpine.data('dashyHoursChart', (labels = [], values = []) => ({
        canvasEl: null,

        init() {
            const canvas = this.$el;
            if (! (canvas instanceof Element)) return;

            this.canvasEl = canvas;
            pendingByCanvas.set(canvas, {
                labels: [...labels],
                values: [...values],
            });

            const blue = cssVar('--blue', '#5992c6');
            const muted = cssVar('--ink-muted', '#a89c89');
            const grid = cssVar('--border', 'rgba(255,255,255,0.06)');

            loadChart().then((Chart) => {
                if (! canvas.isConnected) return;
                if (chartByCanvas.has(canvas)) return;

                const initial = pendingByCanvas.get(canvas) ?? { labels: [], values: [] };

                const chart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: initial.labels,
                        datasets: [
                            {
                                data: initial.values,
                                backgroundColor: blue,
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
                                callbacks: {
                                    label: (ctx) => {
                                        const hours = Number(ctx.parsed.y) || 0;
                                        if (hours <= 0) return '0m';
                                        const whole = Math.floor(hours);
                                        const minutes = Math.round((hours - whole) * 60);
                                        if (whole > 0 && minutes > 0) return `${whole}h ${minutes}m`;
                                        if (whole > 0) return `${whole}h`;
                                        return `${minutes}m`;
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

        update(nextLabels, nextValues) {
            const canvas = this.canvasEl;
            if (! canvas) return;

            const labels = [...(nextLabels ?? [])];
            const values = [...(nextValues ?? [])];

            const chart = chartByCanvas.get(canvas);
            if (! chart) {
                pendingByCanvas.set(canvas, { labels, values });
                return;
            }

            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
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
