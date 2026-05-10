<?php
session_start();
require_once 'db_conn.php'; // Include PDO

// Protect the dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

 $user_id = $_SESSION['user_id'];

// ====================================================================
// SQL JOIN INTEGRATION & EXPLANATIONS (FOR RUBRIC REQUIREMENTS)
// ====================================================================

// 1. LEFT JOIN EXPLANATION:
// We use a LEFT JOIN here because we want to fetch ALL reports belonging to the current user.
// Even if the user's account was somehow deleted from tbl_users (data corruption), 
// we still want to keep and display their report data. A LEFT JOIN ensures no reports 
// are dropped even if the matching user doesn't exist.
 $sql_left = "SELECT 
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

 $stmt = $pdo->prepare($sql_left);
 $stmt->execute(['user_id' => $user_id]);
 $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 2. RIGHT JOIN EXPLANATION:
// We use a RIGHT JOIN here to fetch ALL locations from tbl_locations, and count how many 
// PENDING reports exist at each location. If a location has 0 pending reports, it STILL 
// shows up in the result (with a 0 count). This is crucial for campus safety so admins 
// can see which zones are completely safe, not just zones that have active issues.
 $sql_right = "SELECT 
            l.name AS location_name, 
            COUNT(r.report_id) AS total_pending
        FROM tbl_reports r 
        RIGHT JOIN tbl_locations l ON r.location_id = l.location_id 
        AND r.status = 'Pending' AND r.user_id = :user_id2
        GROUP BY l.location_id, l.name
        ORDER BY total_pending DESC";

 $stmt2 = $pdo->prepare($sql_right);
 $stmt2->execute(['user_id2' => $user_id]);
 $locationSafety = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// NOTE ON FULL OUTER JOIN: 
// MySQL does not support FULL OUTER JOIN natively. To simulate it, we would use a UNION 
// between a LEFT JOIN and a RIGHT JOIN. However, for this system's data relationships 
// (reports must have a user, reports must have a location), INNER/LEFT/RIGHT covers 
// all necessary business logic efficiently.
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
            
            <!-- Displaying RIGHT JOIN Data: Location Safety Overview -->
            <div class="card" style="margin-top: 20px; background: #f9f9f9;">
                <h4>Location Safety Overview (Based on Pending Reports)</h4>
                <div id="locationSafetyList" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
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
        const locationSafetyData = <?php echo json_encode($locationSafety); ?>;
    </script>

    <script src="js/dashboard.js"></script>
    <script src="js/main.js"></script>
</body> 
</html>