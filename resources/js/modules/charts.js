import Chart from 'chart.js/auto';

const chartRegistry = new Map();

window.EduCharts = {
    init(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            return null;
        }

        if (chartRegistry.has(canvasId)) {
            chartRegistry.get(canvasId).destroy();
        }

        const chart = new Chart(canvas, config);
        chartRegistry.set(canvasId, chart);

        return chart;
    },

    line(canvasId, labels, datasets, options = {}) {
        return this.init(canvasId, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { boxWidth: 12, usePointStyle: true, color: '#64748B' },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94A3B8' } },
                    y: { grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8' } },
                },
                ...options,
            },
        });
    },

    doughnut(canvasId, labels, data, colors, options = {}) {
        return this.init(canvasId, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, usePointStyle: true, padding: 16, color: '#64748B' },
                    },
                },
                ...options,
            },
        });
    },
};

export { Chart };