document.addEventListener("DOMContentLoaded", () => {
  const activeUser = localStorage.getItem("activeUser");
  

  const map = L.map("map").setView([8.360179, 124.868653], 15);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"&copy; OpenStreetMap contributors"}).addTo(map);

  const markersLayer = L.layerGroup().addTo(map);
  let defaultData = [];
  let userReports = JSON.parse(localStorage.getItem("reports_" + activeUser) || "[]");

  function renderMarkers() {
    markersLayer.clearLayers();
    function createMarker(spot){
      const popupContent = `
        <div style="text-align:center;">
          <img src="${spot.image||'images/default.jpg'}" style="width:100px; border-radius:6px; margin-bottom:5px;">
          <h3>${spot.name}</h3>
          <p>${spot.desc}</p>
          <p><b>Category:</b> ${spot.type}</p>
        </div>`;
      L.marker([spot.lat, spot.lng]).addTo(markersLayer).bindPopup(popupContent);
    }
    defaultData.forEach(createMarker);
    userReports.forEach(createMarker);
  }

  fetch("data.json").then(r=>r.json()).then(data=>{
    defaultData = data;
    renderMarkers();
  }).catch(()=>{ renderMarkers(); });

  map.on("click", e => {
    const type = prompt("Report Type (e.g., Fire, Flood, Theft):");
    if(!type) return;
    const desc = prompt("Description:");
    if(!desc) return;

    const newReport = {type, desc, date:new Date().toLocaleString(), lat:e.latlng.lat, lng:e.latlng.lng};
    userReports.push(newReport);
    localStorage.setItem("reports_" + activeUser, JSON.stringify(userReports));
    renderMarkers();
    alert("Report added successfully.");

    try{ loadDashboard(); } catch(e){}
    try{ loadReportsTable(); } catch(e){}
  });
});
