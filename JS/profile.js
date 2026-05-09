const user = localStorage.getItem("activeUser");
if(!user){ alert("Please login first."); window.location.href="index.php"; }

document.addEventListener("DOMContentLoaded", () => {
  const data = JSON.parse(localStorage.getItem("user_" + user));
  document.getElementById("profileUser").innerText = data.username;
  document.getElementById("profileSince").innerText = data.created;
});

function exportReports() {
  const reports = localStorage.getItem("reports_" + user) || "[]";
  const blob = new Blob([reports], {type:"application/json"});
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "reports.json";
  a.click();
}