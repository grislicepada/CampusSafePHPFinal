<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CampusSafe - Reports</title>
<link rel="stylesheet" href="style.css">
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
    <button onclick="logout()">Logout</button>
  </div>
</header>

<main class="container">
  <div class="card">
    <h2>Reports (CRUD)</h2>
    <div class="row">
      <input id="reportType" type="text" placeholder="Type (e.g., Flood, Fire, Theft)">
      <input id="reportDesc" type="text" placeholder="Short description">
      <button id="addReportBtn">Add Report</button>
    </div>
    <table id="reportsTable" class="table"></table>
  </div>
</main>

<script src="reports.js"></script>
<script src="main.js"></script>
</body>
</html>