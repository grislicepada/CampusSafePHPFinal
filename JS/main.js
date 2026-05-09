function toggleMenu() {
    const controls = document.getElementById('navControls');
    if (controls) {
        controls.classList.toggle('open');
    }
}

//Core App Functions (Retained)

function showSection(id) {
    document.querySelectorAll("section").forEach(s => s.classList.add("hidden"));
    document.getElementById(id)?.classList.remove("hidden");
}
function logout() {
    localStorage.removeItem("activeUser");
    window.location.href = "index.php";
}

//Consolidated Event Listener 

document.addEventListener('DOMContentLoaded', () => {
    // Dashboard initialization functions
    if (typeof initMap === 'function') {
        initMap(); 
    }
    if (typeof initCharts === 'function') {
        initCharts(); 
    }
    if (typeof populateRecentReports === 'function') {
        populateRecentReports(); 
    }
    
    // Safety Alert Initialization (using 'none' as default for safety)
    if (typeof updateSafetyAlert === 'function') {
        updateSafetyAlert('none', 'All systems normal.'); 
    }
    if (typeof fetchWeatherSnapshot === 'function') {
        fetchWeatherSnapshot(); 
    }
});
