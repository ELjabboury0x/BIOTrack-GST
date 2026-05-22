/* ============================================
   Charts Configuration and Utilities
   ============================================ */

/**
 * Chart Manager Class
 */
class ChartManager {
    constructor() {
        this.charts = {};
        this.chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: '500' }
                    }
                }
            }
        };
        this.colors = {
            primary: '#3b82f6',
            secondary: '#60a5fa',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            purple: '#a855f7',
            pink: '#ec4899',
            teal: '#14b8a6'
        };
    }

    /**
     * Create Line Chart
     */
    createLineChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const defaultOptions = {
            ...this.chartDefaults,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: { grid: { display: false } }
            }
        };

        const config = {
            type: 'line',
            data: data,
            options: { ...defaultOptions, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Create Bar Chart
     */
    createBarChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const defaultOptions = {
            ...this.chartDefaults,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: { grid: { display: false } }
            }
        };

        const config = {
            type: 'bar',
            data: data,
            options: { ...defaultOptions, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Create Pie Chart
     */
    createPieChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const defaultOptions = {
            ...this.chartDefaults,
            plugins: {
                ...this.chartDefaults.plugins,
                datalabels: {
                    formatter: (value, ctx) => {
                        const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = (value * 100 / sum).toFixed(1) + '%';
                        return percentage;
                    }
                }
            }
        };

        const config = {
            type: 'pie',
            data: data,
            options: { ...defaultOptions, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Create Doughnut Chart
     */
    createDoughnutChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const config = {
            type: 'doughnut',
            data: data,
            options: { ...this.chartDefaults, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Create Area Chart
     */
    createAreaChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const defaultOptions = {
            ...this.chartDefaults,
            fill: true,
            tension: 0.4,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: { grid: { display: false } }
            }
        };

        const config = {
            type: 'line',
            data: data,
            options: { ...defaultOptions, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Create Radar Chart
     */
    createRadarChart(canvasId, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const config = {
            type: 'radar',
            data: data,
            options: { ...this.chartDefaults, ...options }
        };

        const chart = new Chart(canvas, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    /**
     * Update Chart
     */
    updateChart(canvasId, newData) {
        const chart = this.charts[canvasId];
        if (!chart) return null;

        chart.data = newData;
        chart.update();
        return chart;
    }

    /**
     * Destroy Chart
     */
    destroyChart(canvasId) {
        const chart = this.charts[canvasId];
        if (chart) {
            chart.destroy();
            delete this.charts[canvasId];
        }
    }

    /**
     * Get Color Palette
     */
    getColorPalette(count = 1) {
        const colors = Object.values(this.colors);
        const palette = [];
        for (let i = 0; i < count; i++) {
            palette.push(colors[i % colors.length]);
        }
        return palette;
    }

    /**
     * Get Theme Colors
     */
    getThemeColors() {
        return this.colors;
    }
}

/**
 * Chart Data Builders
 */
const chartDataBuilders = {
    /**
     * Build Bar Chart Data
     */
    buildBarChartData: function(labels, datasets) {
        return {
            labels: labels,
            datasets: datasets.map(dataset => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.backgroundColor || '#3b82f6',
                borderColor: dataset.borderColor || '#3b82f6',
                borderRadius: 8,
                borderSkipped: false,
                ...dataset
            }))
        };
    },

    /**
     * Build Line Chart Data
     */
    buildLineChartData: function(labels, datasets) {
        return {
            labels: labels,
            datasets: datasets.map(dataset => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: dataset.borderColor || '#3b82f6',
                backgroundColor: dataset.backgroundColor || 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: dataset.borderColor || '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                ...dataset
            }))
        };
    },

    /**
     * Build Pie Chart Data
     */
    buildPieChartData: function(labels, data, backgroundColor) {
        return {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor || [
                    '#3b82f6',
                    '#60a5fa',
                    '#f59e0b',
                    '#10b981',
                    '#ef4444',
                    '#a855f7'
                ],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        };
    },

    /**
     * Build Doughnut Chart Data
     */
    buildDoughnutChartData: function(labels, data, backgroundColor) {
        return this.buildPieChartData(labels, data, backgroundColor);
    }
};

/**
 * Dashboard Charts Initialization
 */
const dashboardCharts = {
    /**
     * Initialize All Charts
     */
    initializeAll: function() {
        this.initializeInterventionsChart();
        this.initializeMaintenanceTypesChart();
        this.initializeCostChart();
    },

    /**
     * Interventions Chart
     */
    initializeInterventionsChart: function() {
        const manager = new ChartManager();
        const data = chartDataBuilders.buildBarChartData(
            ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            [
                {
                    label: 'Interventions Préventive',
                    data: [45, 52, 48, 61, 55, 67, 72, 65, 58, 69, 75, 82],
                    backgroundColor: '#3b82f6'
                },
                {
                    label: 'Interventions Curative',
                    data: [28, 35, 32, 41, 38, 45, 42, 39, 44, 38, 32, 28],
                    backgroundColor: '#60a5fa'
                }
            ]
        );

        manager.createBarChart('interventionsChart', data);
    },

    /**
     * Maintenance Types Chart
     */
    initializeMaintenanceTypesChart: function() {
        const manager = new ChartManager();
        const data = chartDataBuilders.buildPieChartData(
            ['Préventive', 'Curative', 'Corrective'],
            [45, 35, 20],
            ['#3b82f6', '#60a5fa', '#f59e0b']
        );

        manager.createPieChart('maintenanceTypesChart', data);
    },

    /**
     * Cost Chart
     */
    initializeCostChart: function() {
        const manager = new ChartManager();
        const data = chartDataBuilders.buildLineChartData(
            ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            [
                {
                    label: 'Coût Total (DH)',
                    data: [32000, 38500, 35000, 42000, 40000, 48000, 52000, 45000, 42000, 50000, 55000, 62000],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)'
                }
            ]
        );

        manager.createLineChart('costChart', data);
    }
};

/**
 * Real-time Chart Updates
 */
const realtimeCharts = {
    /**
     * Update Chart with New Data
     */
    updateData: function(chartId, newData) {
        const chart = Chart.helpers.getContext(chartId)?.chart;
        if (chart) {
            chart.data = newData;
            chart.update();
        }
    },

    /**
     * Add Data Point
     */
    addDataPoint: function(chartId, label, values) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;

        const chart = window.chartInstances?.[chartId];
        if (chart) {
            chart.data.labels.push(label);
            chart.data.datasets.forEach((dataset, index) => {
                if (values[index] !== undefined) {
                    dataset.data.push(values[index]);
                }
            });
            chart.update();
        }
    }
};

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    dashboardCharts.initializeAll();
});

window.ChartManager = ChartManager;
window.chartDataBuilders = chartDataBuilders;
window.dashboardCharts = dashboardCharts;
window.realtimeCharts = realtimeCharts;
