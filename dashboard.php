<?php
session_start();
require_once 'db_conn.php'; // Include PDO

// Protect the dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// FETCH REPORTS FROM DATABASE USING A JOIN
 $user_id = $_SESSION['user_id'];

// We use LEFT JOIN to connect tbl_reports with tbl_users
// This fetches the report details AND the name of the user who filed it
 $sql = "SELECT 
            r.report_id, 
            r.location_id, 
            r.latitude AS lat, 
            r.longitude AS lng, 
            r.category, 
            r.description, 
            r.status, 
            r.date_reported,
            u.name AS user_name,
            u.email AS user_email
        FROM tbl_reports r 
        LEFT JOIN tbl_users u ON r.user_id = u.user_id 
        WHERE r.user_id = :user_id 
        ORDER BY r.date_reported DESC";

 $stmt = $pdo->prepare($sql);
 $stmt->execute(['user_id' => $user_id]);
 $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusSafe - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header id="topbar">
        <div class="brand">CampusSafe</div>
        <button class="menu-toggle" onclick="toggleMenu()">☰</button>
        <div class="controls" id="navControls">
            <button onclick="window.location.href='dashboard.php'">Dashboard</button>
            <button onclick="window.location.href='weather.php'">Weather</button>
            <button onclick="window.location.href='map.php'">Map</button>
            <button onclick="window.location.href='reports.php'">Reports</button>
            <button onclick="window.location.href='profile.php'">Profile</button>
            <button onclick="window.location.href='about.php'">About</button>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </header>
    
    <main class="container">
        <div class="card">
            <h2>Dashboard</h2>
            <div class="grid">
                <div class="box">
                    <h3>Safety Alert</h3>
                    <p id="safetyAlert">No active alerts.</p>
                </div>
                <div class="box">
                    <h3>Weather Snapshot</h3>
                    <div id="miniWeather">No city searched yet.</div>
                </div>
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <h4>Reports by Type</h4>
                    <canvas id="reportsChart"></canvas>
                </div>
                <div class="chart-card">
                    <h4>Reports Timeline (last 10)</h4>
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h4>Reports Map View</h4>
                <div id="dashboardMap"></div> 
            </div> 
            
            <div class="card small">
                <h4>Recent Reports</h4>
                <ul id="recentReports"></ul>
            </div>
        </div>
    </main> 
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- PASS DATABASE RECORDS TO JAVASCRIPT -->
    <script>
        const currentUser = "<?php echo $_SESSION['name']; ?>";
        const userReports = <?php echo json_encode($reports); ?>;
    </script>

    <script src="js/dashboard.js"></script>
    <script src="js/main.js"></script>
</body> 
</html>