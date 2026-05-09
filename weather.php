<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CampusSafe - Weather</title>
<link rel="stylesheet" href="style.css">
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
    <h2>Weather Information (OpenWeather)</h2>
    <div class="row">
      <input id="cityInput" placeholder="Enter city (e.g., Malaybalay)" />
      <button id="searchWeatherBtn">Search</button>
    </div>
    <div id="weatherBox" class="weather-box">No data yet.</div>
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

document.getElementById("searchWeatherBtn").addEventListener("click", () => {
  const city = document.getElementById("cityInput").value.trim();
  if(!city) return alert("Enter a city name");
  const apiKey = "9d9f6f36546d0442f55deeb57e8b9553"; // replace with your OpenWeather API

  fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
    .then(res => res.json())
    .then(data => {
      if(data.cod !== 200) return alert("City not found");
      document.getElementById("weatherBox").innerHTML =
        `<h3>${data.name}</h3><p>Temp: ${data.main.temp}°C</p><p>Weather: ${data.weather[0].description}</p>`;
      localStorage.setItem("lastWeather_" + getUserReportsKey(), `${data.name}: ${data.main.temp}°C`);
      try { loadDashboard(); } catch(e){}
    });
});
</script>
</body>
</html>
