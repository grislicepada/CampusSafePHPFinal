<?php
session_start();
require_once 'db_conn.php';

// Protect the page
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

 $user_id = $_SESSION['user_id'];

// Fetch user reports that have coordinates
 $sql = "SELECT category, description, latitude, longitude FROM tbl_reports WHERE user_id = :user_id AND latitude IS NOT NULL";
 $stmt = $pdo->prepare($sql);
 $stmt->execute(['user_id' => $user_id]);
 $db_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CampusSafe - Map</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<header id="topbar">
  <div class="brand">CampusSafe</div>
  <div class="controls">
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
    <h2>Campus Map</h2>
    <div id="map" style="height:500px;"></div>
    <p>Click anywhere on the map to create a new safety report.</p>
  </div>
</main>

<!-- Pass the PHP database records to JavaScript -->
<script>
  const dbUserReports = <?php echo json_encode($db_reports); ?>;
</script>

<script src="js/map.js"></script>
</body>
</html>