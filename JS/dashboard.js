let reportsChart, timelineChart;

document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

// --- Main Dashboard Loading Function ---
function loadDashboard() {
    // CHANGED: Check the variable passed from PHP instead of LocalStorage
    if (typeof currentUser === 'undefined' || !currentUser) {
        alert("Please login first.");
        window.location.href = "login.php";
        return;
    }

    // CHANGED: Use the PHP-injected currentUser variable
    const user = currentUser; 
    const reports = JSON.parse(localStorage.getItem("reports_" + user) || "[]");

    updateSafetyAlert('none', 'All systems normal.');

    fetchWeatherSnapshot();

    // Recent Reports
    const list = document.getElementById("recentReports");
    list.innerHTML = reports.slice(-5).reverse()
        .map(r => `<li>${r.type} – ${r.desc} (${r.date})</li>`).join("");

    // --- Charts ---
    const typeCount = {};
    const reportDates = {};

    reports.forEach(r => {
        typeCount[r.type] = (typeCount[r.type] || 0) + 1;
        reportDates[r.date] = (reportDates[r.date] || 0) + 1;
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

    // CHANGED: Use the PHP-injected currentUser variable
    const user = currentUser;
    const reports = JSON.parse(localStorage.getItem("reports_" + user) || "[]");

    function createMarker(spot) {
        const popup = `
            <div style="font-family: 'Segoe UI'; padding: 5px;">
                <h4 style="margin:0; color:#00796B;">${spot.name}</h4>
                <p>${spot.desc}</p>
                <strong>Type: ${spot.type}</strong>
            </div>
        `;
        L.marker([spot.lat, spot.lng]).addTo(markersLayer).bindPopup(popup);
    }

    // Load user reports
    reports.forEach(r => {
        if (r.lat && r.lng) {
            createMarker({
                name: r.type,
                desc: r.desc,
                type: `User Report (${r.date})`,
                lat: r.lat,
                lng: r.lng
            });
        }
    });

    // Add new report on click
    map.on('click', function (e) {
        const type = prompt("Enter report type:");
        const desc = prompt("Enter report description:");
        if (!type || !desc) return;

        const newReport = {
            type, desc,
            date: new Date().toLocaleDateString(),
            lat: e.latlng.lat,
            lng: e.latlng.lng
        };

        reports.push(newReport);
        localStorage.setItem("reports_" + user, JSON.stringify(reports));

        createMarker(newReport);
        loadDashboard();
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