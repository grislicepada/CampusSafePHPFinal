<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CampusSafe - Map</title>
<link rel="stylesheet" href="style.css">
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
    <button onclick="logout()">Logout</button>
  </div>
</header>

<main class="container">
  <div class="card">
    <h2>Campus Map</h2>
    <div id="map" style="height:500px;"></div>
    <p>Click anywhere on the map to create a new safety report.</p>
  </div>
</main>

<script src="map.js"></script>
</body>
</html>
