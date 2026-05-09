document.getElementById("searchWeatherBtn")?.addEventListener("click", () => {
  const city = document.getElementById("cityInput").value.trim();
  if(!city) return alert("Enter a city name");

  const apiKey = "YOUR_OPENWEATHER_API_KEY"; // replace
  fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
    .then(res=>res.json())
    .then(data=>{
      if(data.cod!==200) return alert("City not found");

      const box = document.getElementById("weatherBox");
      box.innerHTML = `<h3>${data.name}</h3><p>Temp: ${data.main.temp}°C</p><p>Weather: ${data.weather[0].description}</p>`;
      localStorage.setItem("lastWeather", `${data.name}: ${data.main.temp}°C`);
      try{ loadDashboard(); } catch(e){}
    });
});
