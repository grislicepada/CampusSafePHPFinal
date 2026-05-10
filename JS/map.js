document.addEventListener("DOMContentLoaded", () => {
  const map = L.map("map").setView([8.360179, 124.868653], 15);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"&copy; OpenStreetMap contributors"}).addTo(map);

  const markersLayer = L.layerGroup().addTo(map);
  let defaultData = [];

  function renderMarkers() {
    markersLayer.clearLayers();

    // 1. Render Default Campus Locations (from data.json)
    defaultData.forEach(spot => {
      const popupContent = `
        <div style="text-align:center;">
          <img src="${spot.image||'images/default.jpg'}" style="width:100px; border-radius:6px; margin-bottom:5px;">
          <h3>${spot.name}</h3>
          <p>${spot.desc}</p>
          <p><b>Category:</b> ${spot.type}</p>
        </div>`;
      L.marker([spot.lat, spot.lng]).addTo(markersLayer).bindPopup(popupContent);
    });

    // 2. Render User's DB Reports (from map.php)
    if (typeof dbUserReports !== 'undefined') {
      dbUserReports.forEach(r => {
        const popupContent = `
          <div style="text-align:center;">
            <h3 style="color:#00796B; margin:0;">${r.category}</h3>
            <p>${r.description}</p>
          </div>`;
        L.marker([r.latitude, r.longitude]).addTo(markersLayer).bindPopup(popupContent);
      });
    }
  }

  fetch("data.json").then(r=>r.json()).then(data=>{
    defaultData = data;
    renderMarkers();
  }).catch(()=>{ renderMarkers(); });

  // 3. Handle Map Click -> Send to DATABASE!
  map.on("click", async e => {
    const category = prompt("Report Type (e.g., Fire, Flood, Theft):");
    if(!category) return;
    const description = prompt("Description:");
    if(!description) return;

    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    try {
        // Send data to our new PHP file using Fetch API
        const formData = new FormData();
        formData.append('category', category);
        formData.append('description', description);
        formData.append('lat', lat);
        formData.append('lng', lng);

        const response = await fetch('api_add_report.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            // Add the new marker directly to the map so you see it instantly
            const popupContent = `
              <div style="text-align:center;">
                <h3 style="color:#00796B; margin:0;">${category}</h3>
                <p>${description}</p>
              </div>`;
            L.marker([lat, lng]).addTo(markersLayer).bindPopup(popupContent);
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        alert("An error occurred while saving the report.");
        console.error(error);
    }
  });
});