// Gestion des graphiques de monitoring CPU/RAM avec Chart.js

// Configuration globale Chart.js
Chart.defaults.color = '#666';
Chart.defaults.borderColor = '#e0e0e0';
Chart.defaults.font.family = 'tahoma, arial, helvetica, lucida sans, sans-serif';

// Variables globales pour les graphiques
let gaugeChartCpu = null;
let gaugeChartRam = null;
let realtimeChart = null;
let historyChart = null;

// Configuration des couleurs dynamiques selon le pourcentage
function getColorForValue(value) {
    if (value < 60) return '#43a047'; // Vert
    if (value < 80) return '#ff9800'; // Orange
    return '#e53935'; // Rouge
}

// Initialisation des graphiques au chargement
function initMonitoringCharts() {
    initGaugeCharts();
    initRealtimeChart();
    initHistoryChart();

    // Premier chargement des données
    updateMonitoringData();

    // Rafraîchissement automatique
    setInterval(updateMonitoringData, REFRESH_INTERVAL || 30000);
}

// Création des jauges circulaires
function initGaugeCharts() {
    const commonConfig = {
        type: 'doughnut',
        options: {
            responsive: true,
            maintainAspectRatio: true,
            circumference: 180,
            rotation: -90,
            cutout: '75%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
            },
            animation: {
                animateRotate: true,
                animateScale: false,
            },
        },
    };

    // Jauge CPU
    const ctxCpu = document.getElementById('gauge-cpu');
    if (ctxCpu) {
        gaugeChartCpu = new Chart(ctxCpu, {
            ...commonConfig,
            data: {
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#ccc', '#f0f0f0'],
                    borderWidth: 0,
                }],
            },
            plugins: [{
                id: 'gaugeText',
                afterDraw: (chart) => {
                    const { ctx, width, height } = chart;
                    ctx.restore();
                    const fontSize = (height / 180).toFixed(2);
                    ctx.font = `bold ${fontSize}em tahoma`;
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = chart.data.datasets[0].backgroundColor[0];
                    const text = chart.data.datasets[0].data[0] + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height - height / 4;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                },
            }],
        });
    }

    // Jauge RAM
    const ctxRam = document.getElementById('gauge-ram');
    if (ctxRam) {
        gaugeChartRam = new Chart(ctxRam, {
            ...commonConfig,
            data: {
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#ccc', '#f0f0f0'],
                    borderWidth: 0,
                }],
            },
            plugins: [{
                id: 'gaugeText',
                afterDraw: (chart) => {
                    const { ctx, width, height } = chart;
                    ctx.restore();
                    const fontSize = (height / 180).toFixed(2);
                    ctx.font = `bold ${fontSize}em tahoma`;
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = chart.data.datasets[0].backgroundColor[0];
                    const text = chart.data.datasets[0].data[0] + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height - height / 4;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                },
            }],
        });
    }
}

// Création du graphique temps réel (Line)
function initRealtimeChart() {
    const ctx = document.getElementById('chart-realtime');
    if (!ctx) return;

    realtimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'CPU (%)',
                    data: [],
                    borderColor: '#e53935',
                    backgroundColor: 'rgba(229, 57, 53, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'RAM (%)',
                    data: [],
                    borderColor: '#1e88e5',
                    backgroundColor: 'rgba(30, 136, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%',
                    },
                },
                x: {
                    display: true,
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 10,
                    },
                },
            },
            animation: {
                duration: 750,
            },
        },
    });
}

// Création du graphique historique (Bar)
function initHistoryChart() {
    const ctx = document.getElementById('chart-history');
    if (!ctx) return;

    historyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'CPU Moy.',
                    data: [],
                    backgroundColor: 'rgba(229, 57, 53, 0.6)',
                    borderColor: '#e53935',
                    borderWidth: 1,
                },
                {
                    label: 'RAM Moy.',
                    data: [],
                    backgroundColor: 'rgba(30, 136, 229, 0.6)',
                    borderColor: '#1e88e5',
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: (value) => value + '%',
                    },
                },
            },
        },
    });
}

// Mise à jour des données depuis l'API
function updateMonitoringData() {
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';

    fetch(baseUrl + '/api/monitoring/current')
        .then(response => response.json())
        .then(data => {
            // Mise à jour des jauges
            updateGauges(data.current.cpu, data.current.ram);

            // Mise à jour graphique temps réel
            updateRealtimeChart(data.history);

            // Mise à jour graphique historique
            updateHistoryChart(data.history);
        })
        .catch(error => {
            console.error('Erreur monitoring:', error);
        });
}

// Mise à jour des jauges
function updateGauges(cpu, ram) {
    if (gaugeChartCpu && cpu !== null) {
        const cpuColor = getColorForValue(cpu);
        gaugeChartCpu.data.datasets[0].data = [cpu, 100 - cpu];
        gaugeChartCpu.data.datasets[0].backgroundColor = [cpuColor, '#f0f0f0'];
        gaugeChartCpu.update('none');
    }

    if (gaugeChartRam && ram !== null) {
        const ramColor = getColorForValue(ram);
        gaugeChartRam.data.datasets[0].data = [ram, 100 - ram];
        gaugeChartRam.data.datasets[0].backgroundColor = [ramColor, '#f0f0f0'];
        gaugeChartRam.update('none');
    }
}

// Mise à jour graphique temps réel
function updateRealtimeChart(history) {
    if (!realtimeChart) return;

    const now = Date.now() / 1000;

    // Préparer les labels (temps relatif)
    const labels = history.cpu.map(point => {
        const diff = Math.round(now - point.timestamp);
        if (diff < 60) return `${diff}s`;
        return `${Math.round(diff / 60)}m`;
    });

    // Extraire les valeurs
    const cpuData = history.cpu.map(p => p.value);
    const ramData = history.ram.map(p => p.value);

    // Mettre à jour le graphique
    realtimeChart.data.labels = labels;
    realtimeChart.data.datasets[0].data = cpuData;
    realtimeChart.data.datasets[1].data = ramData;
    realtimeChart.update('active');
}

// Mise à jour graphique historique (moyennes par périodes de 5 min)
function updateHistoryChart(history) {
    if (!historyChart) return;

    // Agréger par périodes de 5 minutes (10 points à 30s = 5 min)
    const aggregated = aggregateByPeriod(history, 10);

    historyChart.data.labels = aggregated.labels;
    historyChart.data.datasets[0].data = aggregated.cpu;
    historyChart.data.datasets[1].data = aggregated.ram;
    historyChart.update('active');
}

// Agrégation des données par périodes
function aggregateByPeriod(history, pointsPerPeriod) {
    const result = { labels: [], cpu: [], ram: [] };
    const now = Date.now() / 1000;

    for (let i = 0; i < history.cpu.length; i += pointsPerPeriod) {
        const cpuSlice = history.cpu.slice(i, i + pointsPerPeriod);
        const ramSlice = history.ram.slice(i, i + pointsPerPeriod);

        if (cpuSlice.length === 0) continue;

        const cpuAvg = cpuSlice.reduce((sum, p) => sum + p.value, 0) / cpuSlice.length;
        const ramAvg = ramSlice.reduce((sum, p) => sum + p.value, 0) / ramSlice.length;

        const time = cpuSlice[0].timestamp;
        const diff = Math.round((now - time) / 60);
        const label = diff < 60 ? `-${diff}m` : `-${Math.round(diff / 60)}h`;

        result.labels.push(label);
        result.cpu.push(cpuAvg);
        result.ram.push(ramAvg);
    }

    return result;
}

// Adapter le thème des graphiques au mode sombre
function updateChartsTheme() {
    const isDark = document.body.classList.contains('dark-theme');

    Chart.defaults.color = isDark ? '#e0e0e0' : '#666';
    Chart.defaults.borderColor = isDark ? '#333' : '#e0e0e0';

    // Redessiner tous les graphiques
    if (gaugeChartCpu) gaugeChartCpu.update('none');
    if (gaugeChartRam) gaugeChartRam.update('none');
    if (realtimeChart) realtimeChart.update('none');
    if (historyChart) historyChart.update('none');
}

// Initialiser au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMonitoringCharts);
} else {
    initMonitoringCharts();
}

// Écouter les changements de thème
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateChartsTheme);
