<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CampusSafe - About</title>
<link rel="stylesheet" href="css/style.css">
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
    <h2>ABOUT</h2>
    <p>CampusSafe is an integrated platform that helps NBSC students and personnel stay informed and safe.
    Users can report incidents such as fires, floods, or thefts, view real-time weather information, and track events on an interactive campus map.
    The system provides dashboards for monitoring trends and ensures quick access to critical alerts. 
    Built with modern web technologies, it emphasizes accessibility, usability, and security.</p>
<h3>TEAM MEMBERS</h3>

<div class="team-grid">

  <div class="team-card">
    <img src="/images/FPF.png" alt="Ellaiza Jean Balatero">
    <h4>Ellaiza Jean Balatero</h4>
    <p class="role">Web System Developer</p>
    <p class="tag">Handled Subject: Web System</p>
  </div>

  <div class="team-card">
    <img src="/images/grisli.jpg" alt="Gracelie Mae Cepada">
    <h4>Gracelie Mae Cepada</h4>
    <p class="role">OOP Specialist</p>
    <p class="tag">Handled Subject: OOP</p>
  </div>
</div>

</main>

<script src="main.js"></script>
</body>
</html>
