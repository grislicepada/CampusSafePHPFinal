let reportsChart, timelineChart;

document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

// --- Main Dashboard Loading Function ---
function loadDashboard() {
    // CHANGED: Use the PHP-injected userReports from the database
    const reports = userReports; 

    updateSafetyAlert('none', 'All systems normal.');
    fetchWeatherSnapshot();

    // --- UPDATED RECENT REPORTS LIST ---
    const list = document.getElementById("recentReports");
    if (reports.length > 0) {
        list.innerHTML = reports.slice(0, 5) // Get top 5 recent
            .map(r => {
                // Check if the report is Completed or Pending
                const textStyle = r.status === 'Completed' ? 'text-decoration: line-through; opacity: 0.6;' : '';
                const statusBadge = r.status === 'Completed' 
                    ? '<span style="color:#4CAF50; font-size:0.85em;">✔ Completed</span>' 
                    : '<span style="color:#FF9800; font-size:0.85em;">⏳ Pending</span>';
                
                return `<li style="${textStyle}"><strong>${r.category}</strong> – ${r.description} ${statusBadge} (${new Date(r.date_reported).toLocaleDateString()})</li>`;
            }).join("");
    } else {
        list.innerHTML = "<li>No reports filed yet.</li>";
    }
    // --- END OF UPDATED SECTION ---

    // --- Charts ---
    const typeCount = {};
    const reportDates = {};

    reports.forEach(r => {
        // Count by Category
        typeCount[r.category] = (typeCount[r.category] || 0) + 1;
        
        // Group by Date
        const dateStr = new Date(r.date_reported).toLocaleDateString();
        reportDates[dateStr] = (reportDates[dateStr] || 0) + 1;
    });

    // Doughnut Chart
    const ctx1 = document.getElementById("reportsChart");
    reportsChart?.destroy();
    reportsChart = new Chart(ctx1, {
        type: "doughnut",
        data: {
            labels: Object.keys(typeCount),
            datasets: [{
                data: Object.values(typeCount),
                backgroundColor: ['#00796B', '#004D40', '#FF9800', '#F44336', '#666666'],
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Timeline Chart
    const timelineLabels = Object.keys(reportDates).slice(-10);
    const timelineData = timelineLabels.map(d => reportDates[d]);

    const ctx2 = document.getElementById("timelineChart");
    timelineChart?.destroy();
    timelineChart = new Chart(ctx2, {
        type: "bar",
        data: { labels: timelineLabels, datasets: [{ label: "Reports Filed", data: timelineData, backgroundColor: '#00796B' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Load Map
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
    const reports = userReports; // Use database records

    // Load user reports markers
    reports.forEach(r => {
        if (r.lat && r.lng) { 
            // Added color coding for the map popup status as well!
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