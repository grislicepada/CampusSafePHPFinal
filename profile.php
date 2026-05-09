<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CampusSafe - Profile</title>
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
    <h2>Profile</h2>
    <p><strong>Logged in as:</strong> <span id="profileUser">-</span></p>
    <p><strong>Member since:</strong> <span id="profileSince">-</span></p>
    <button onclick="exportReports()">Export Reports (JSON)</button>
  </div>
</main>

<script>
function getUserReportsKey() {
  return "reports_" + localStorage.getItem("activeUser");
}

function logout() {
  localStorage.removeItem("activeUser");
  window.location.href = "index.php";
}

function loadProfile() {
  const user = localStorage.getItem("activeUser");
  if(!user) { logout(); return; }
  const data = JSON.parse(localStorage.getItem("user_" + user));
  document.getElementById("profileUser").innerText = data.username;
  document.getElementById("profileSince").innerText = data.created;
}

function exportReports() {
  const data = localStorage.getItem(getUserReportsKey()) || "[]";
  const blob = new Blob([data], {type:"application/json"});
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "reports.json";
  a.click();
}

document.addEventListener("DOMContentLoaded", loadProfile);
</script>
</body>
</html>
