let reportsChart, timelineChart;

document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

// --- Main Dashboard Loading Function ---
function loadDashboard() {
    const reports = userReports; 

    // ==========================================
    // UPGRADED: SMART SAFETY ALERT LOGIC
    // ==========================================
    const dangerousCategories = ['Fire', 'Flood', 'Earthquake', 'Typhoon'];
    const warningCategories = ['Theft', 'Hazard', 'Accident', 'Construction'];

    let hasHighAlert = false;
    let hasMediumAlert = false;
    let highAlertMessage = "";
    let mediumAlertMessage = "";

    // Check all reports for pending dangers
    reports.forEach(r => {
        const category = r.category.toLowerCase();
        const isDangerous = dangerousCategories.some(d => category.includes(d.toLowerCase()));
        const isWarning = warningCategories.some(w => category.includes(w.toLowerCase()));

        if (r.status === 'Pending' || r.status === 'Ongoing') {
            if (isDangerous) {
                hasHighAlert = true;
                highAlertMessage = `Active ${r.category} reported! Avoid the area.`;
            } else if (isWarning) {
                hasMediumAlert = true;
                mediumAlertMessage = `Advisory: ${r.category} reported in the area.`;
            }
        }
    });

    // Set the alert based on what was found
    if (hasHighAlert) {
        updateSafetyAlert('active', highAlertMessage);
    } else if (hasMediumAlert) {
        updateSafetyAlert('medium', mediumAlertMessage);
    } else {
        updateSafetyAlert('none', 'All systems normal.');
    }
    // ==========================================

    fetchWeatherSnapshot();

    // --- Recent Reports List with Strikethrough ---
    const list = document.getElementById("recentReports");
    if (reports.length > 0) {
        list.innerHTML = reports.slice(0, 5)
            .map(r => {
                const textStyle = r.status === 'Completed' ? 'text-decoration: line-through; opacity: 0.6;' : '';
                const statusBadge = r.status === 'Completed' 
                    ? '<span style="color:#4CAF50; font-size:0.85em;">✔ Completed</span>' 
                    : '<span style="color:#FF9800; font-size:0.85em;">⏳ Pending</span>';
                
                return `<li style="${textStyle}"><strong>${r.category}</strong> – ${r.description} ${statusBadge} (${new Date(r.date_reported).toLocaleDateString()})</li>`;
            }).join("");
    } else {
        list.innerHTML = "<li>No reports filed yet.</li>";
    }

    // --- UPGRADED CHARTS DATA ---
    const typeCount = {};
    const reportDatesStatus = {}; 

    reports.forEach(r => {
        typeCount[r.category] = (typeCount[r.category] || 0) + 1;
        
        const dateStr = new Date(r.date_reported).toLocaleDateString();
        if (!reportDatesStatus[dateStr]) {
            reportDatesStatus[dateStr] = { Pending: 0, Completed: 0 };
        }
        
        if (r.status === 'Completed') {
            reportDatesStatus[dateStr].Completed++;
        } else {
            reportDatesStatus[dateStr].Pending++;
        }
    });

    // --- UPGRADED DOUGHNUT CHART ---
    const ctx1 = document.getElementById("reportsChart");
    reportsChart?.destroy();
    
    const doughnutColors = ['#00796B', '#26A69A', '#FF9800', '#F44336', '#7986CB', '#FFD54F', '#90A4AE'];
    
    reportsChart = new Chart(ctx1, {
        type: "doughnut",
        data: {
            labels: Object.keys(typeCount),
            datasets: [{
                data: Object.values(typeCount),
                backgroundColor: doughnutColors,
                borderWidth: 2,
                borderColor: '#FFFFFF',
                hoverOffset: 8
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, font: { size: 12 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // --- UPGRADED STACKED BAR CHART ---
    const timelineLabels = Object.keys(reportDatesStatus).slice(-10);
    const pendingData = timelineLabels.map(d => reportDatesStatus[d]?.Pending || 0);
    const completedData = timelineLabels.map(d => reportDatesStatus[d]?.Completed || 0);

    const ctx2 = document.getElementById("timelineChart");
    timelineChart?.destroy();
    timelineChart = new Chart(ctx2, {
        type: "bar", 
        data: { 
            labels: timelineLabels, 
            datasets: [
                {
                    label: "Pending", 
                    data: pendingData, 
                    backgroundColor: '#FF9800',
                    borderRadius: 4,
                    borderSkipped: false
                },
                {
                    label: "Completed", 
                    data: completedData, 
                    backgroundColor: '#4CAF50',
                    borderRadius: 4,
                    borderSkipped: false
                }
            ] 
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#E0E0E0' }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, font: { size: 12 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)'
                }
            }
        }
    });

    setTimeout(loadDashboardMap, 100);
}

// --- MAP FUNCTION ---
function loadDashboardMap() {
    const container = document.getElementById("dashboardMap");
    if (!container || typeof L === 'undefined') return;

    const map = L.map(container).setView([8.360179, 124.868653], 15);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);
    const reports = userReports; 

    reports.forEach(r => {
        if (r.lat && r.lng) { 
            const statusColor = r.status === 'Completed' ? '#4CAF50' : '#FF9800';
            const popup = `
                <div style="font-family: 'Segoe UI'; padding: 5px;">
                    <h4 style="margin:0; color:#00796B;">${r.category}</h4>
                    <p>${r.description}</p>
                    <small style="color:#666;">Date: ${new Date(r.date_reported).toLocaleDateString()}</small><br>
                    <strong style="color:${statusColor};">Status: ${r.status}</strong>
                </div>
            `;
            L.marker([r.lat, r.lng]).addTo(markersLayer).bindPopup(popup);
        }
    });
}

// --- Safety Alert ---
function updateSafetyAlert(alertStatus, message) {
    const alertElement = document.getElementById('safetyAlert');
    const alertBox = alertElement.closest('.box');

    alertBox.classList.remove('alert-low', 'alert-medium', 'alert-high');

    if (alertStatus === 'active') {
        alertBox.classList.add('alert-high');
        alertElement.textContent = `🔴 ALERT ACTIVE: ${message}`;
    } else if (alertStatus === 'medium') {
        alertBox.classList.add('alert-medium');
        alertElement.textContent = `🟠 ADVISORY: ${message}`;
    } else {
        alertBox.classList.add('alert-low');
        alertElement.textContent = '🟢 No active alerts.';
    }
}

// --- Weather ---
async function fetchWeatherSnapshot() {
    const API_KEY = '9d9f6f36546d0442f55deeb57e8b9553';
    const LAT = '8.360179', LON = '124.868653';

    const URL = `https://api.openweathermap.org/data/2.5/weather?lat=${LAT}&lon=${LON}&appid=${API_KEY}&units=metric`;

    try {
        const data = await (await fetch(URL)).json();
        displayWeatherSnapshot(data);
    } catch (e) {
        console.error(e);
        document.getElementById('miniWeather').innerText = 'Weather load failed.';
    }
}

function displayWeatherSnapshot(data) {
    const html = `
        <div class="weather-content">
            <img src="https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png">
            <div>
                <h3>${data.name}</h3>
                <p>${Math.round(data.main.temp)}°C</p>
                <p>${data.weather[0].description}</p>
            </div>
        </div>
    `;
    document.getElementById('miniWeather').innerHTML = html;
}