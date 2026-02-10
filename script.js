// API Configuration
const API_BASE = './'; // Adjust this to your server path
const UPDATE_INTERVAL = 5000; // Update every 5 seconds

// Chart instance
let weatherChart = null;
let currentTimeRange = 1; // hours

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadCurrentData();
    loadStats();
    loadAlerts();
    loadHistoricalData(currentTimeRange);
    
    // Set up auto-refresh
    setInterval(loadCurrentData, UPDATE_INTERVAL);
    setInterval(() => loadHistoricalData(currentTimeRange), UPDATE_INTERVAL * 2);
    setInterval(loadAlerts, UPDATE_INTERVAL * 3);
    setInterval(loadStats, UPDATE_INTERVAL * 4);
    
    // Time range buttons
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentTimeRange = parseInt(this.dataset.hours);
            loadHistoricalData(currentTimeRange);
        });
    });
});

// Load current sensor data
async function loadCurrentData() {
    try {
        const response = await fetch(`${API_BASE}api_get_data.php?type=current`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const { temperature, humidity, timestamp } = result.data;
            
            // Update display with animation
            updateValue('temperatureValue', temperature.toFixed(1));
            updateValue('humidityValue', humidity.toFixed(1));
            
            // Update status
            updateStatus(true);
            
            // Update last update time
            document.getElementById('lastUpdate').textContent = formatTime(timestamp);
        } else {
            updateStatus(false);
        }
    } catch (error) {
        console.error('Error loading current data:', error);
        updateStatus(false);
    }
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch(`${API_BASE}api_get_data.php?type=stats&hours=24`);
        const result = await response.json();
        
        if (result.success && result.stats) {
            const { temperature, humidity, reading_count } = result.stats;
            
            // Update temperature stats
            updateValue('tempMin', temperature.min.toFixed(1));
            updateValue('tempAvg', temperature.avg.toFixed(1));
            updateValue('tempMax', temperature.max.toFixed(1));
            
            // Update humidity stats
            updateValue('humidityMin', humidity.min.toFixed(1));
            updateValue('humidityAvg', humidity.avg.toFixed(1));
            updateValue('humidityMax', humidity.max.toFixed(1));
            
            // Update reading count
            document.getElementById('readingCount').textContent = reading_count;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load historical data
async function loadHistoricalData(hours) {
    try {
        const response = await fetch(`${API_BASE}api_get_data.php?type=history&hours=${hours}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            updateChart(result.data);
        }
    } catch (error) {
        console.error('Error loading historical data:', error);
    }
}

// Load alerts
async function loadAlerts() {
    try {
        const response = await fetch(`${API_BASE}api_get_data.php?type=alerts`);
        const result = await response.json();
        
        if (result.success) {
            displayAlerts(result.alerts);
            
            // Show banner for latest alert
            if (result.alerts.length > 0) {
                showAlertBanner(result.alerts[0].message);
            }
        }
    } catch (error) {
        console.error('Error loading alerts:', error);
    }
}

// Display alerts in list
function displayAlerts(alerts) {
    const alertsList = document.getElementById('alertsList');
    const alertCount = document.getElementById('alertCount');
    
    alertCount.textContent = alerts.length;
    
    if (alerts.length === 0) {
        alertsList.innerHTML = '<div class="no-alerts">No alerts at this time</div>';
        return;
    }
    
    alertsList.innerHTML = alerts.map(alert => `
        <div class="alert-item" data-alert-id="${alert.id}">
            <div class="alert-info">
                <div class="alert-type">${formatAlertType(alert.type)}</div>
                <div class="alert-text">${alert.message}</div>
                <div class="alert-time">${formatTime(alert.timestamp)}</div>
            </div>
            <button class="alert-dismiss" onclick="dismissAlert(${alert.id})">Dismiss</button>
        </div>
    `).join('');
}

// Dismiss alert
async function dismissAlert(alertId) {
    try {
        const response = await fetch(`${API_BASE}api_alerts.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ alert_id: alertId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove alert from DOM with animation
            const alertItem = document.querySelector(`[data-alert-id="${alertId}"]`);
            if (alertItem) {
                alertItem.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    alertItem.remove();
                    loadAlerts(); // Reload to update count
                }, 300);
            }
        }
    } catch (error) {
        console.error('Error dismissing alert:', error);
    }
}

// Initialize chart
function initializeChart() {
    const ctx = document.getElementById('weatherChart').getContext('2d');
    
    weatherChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: [],
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Humidity (%)',
                    data: [],
                    borderColor: '#4ecdc4',
                    backgroundColor: 'rgba(78, 205, 196, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    yAxisID: 'y1'
                }
            ]
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
                    labels: {
                        color: '#94a3b8',
                        font: {
                            family: 'Outfit',
                            size: 12
                        },
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#e4e9f2',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(148, 163, 184, 0.2)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y.toFixed(1);
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(148, 163, 184, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            family: 'JetBrains Mono',
                            size: 10
                        }
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(148, 163, 184, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#ff6b6b',
                        font: {
                            family: 'JetBrains Mono',
                            size: 10
                        }
                    },
                    title: {
                        display: true,
                        text: 'Temperature (°C)',
                        color: '#ff6b6b'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#4ecdc4',
                        font: {
                            family: 'JetBrains Mono',
                            size: 10
                        }
                    },
                    title: {
                        display: true,
                        text: 'Humidity (%)',
                        color: '#4ecdc4'
                    }
                }
            }
        }
    });
}

// Update chart data
function updateChart(data) {
    if (!weatherChart || !data || data.length === 0) return;
    
    const labels = data.map(d => formatChartTime(d.timestamp));
    const temperatures = data.map(d => d.temperature);
    const humidities = data.map(d => d.humidity);
    
    weatherChart.data.labels = labels;
    weatherChart.data.datasets[0].data = temperatures;
    weatherChart.data.datasets[1].data = humidities;
    weatherChart.update('none'); // Update without animation for real-time feel
}

// Update value with animation
function updateValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const currentValue = element.textContent;
    if (currentValue !== value.toString()) {
        element.style.animation = 'none';
        setTimeout(() => {
            element.textContent = value;
            element.style.animation = 'valueChange 0.5s ease';
        }, 10);
    }
}

// Update connection status
function updateStatus(online) {
    const indicator = document.getElementById('statusIndicator');
    const text = document.getElementById('statusText');
    
    if (online) {
        indicator.classList.add('online');
        text.textContent = 'Connected';
    } else {
        indicator.classList.remove('online');
        text.textContent = 'Offline';
    }
}

// Show alert banner
function showAlertBanner(message) {
    const banner = document.getElementById('alertBanner');
    const messageEl = document.getElementById('alertMessage');
    
    messageEl.textContent = message;
    banner.style.display = 'block';
}

// Close alert banner
function closeAlertBanner() {
    const banner = document.getElementById('alertBanner');
    banner.style.animation = 'slideUp 0.3s ease';
    setTimeout(() => {
        banner.style.display = 'none';
        banner.style.animation = '';
    }, 300);
}

// Format time for display
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMinutes = Math.floor((now - date) / 60000);
    
    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)}h ago`;
    
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format time for chart
function formatChartTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format alert type
function formatAlertType(type) {
    const types = {
        'temp_high': 'High Temperature',
        'temp_low': 'Low Temperature',
        'humidity_high': 'High Humidity',
        'humidity_low': 'Low Humidity'
    };
    return types[type] || type;
}

// Add animation for value changes
const style = document.createElement('style');
style.textContent = `
    @keyframes valueChange {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    @keyframes slideUp {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100%); }
    }
`;
document.head.appendChild(style);
