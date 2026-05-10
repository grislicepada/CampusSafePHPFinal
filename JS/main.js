function toggleMenu() {
    const controls = document.getElementById('navControls');
    if (controls) {
        controls.classList.toggle('open');
    }
}

function logout() {
    // Redirect to PHP to properly destroy the session
    window.location.href = "logout.php";
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
    
    // // Safety Alert Initialization (using 'none' as default for safety)
    // if (typeof updateSafetyAlert === 'function') {
    //     updateSafetyAlert('none', 'All systems normal.'); 
    // }
    if (typeof fetchWeatherSnapshot === 'function') {
        fetchWeatherSnapshot(); 
    }
});
