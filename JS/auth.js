// FIX: Function to toggle password visibility
function togglePassVisibility(id, element) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        element.textContent = "🔒";
    } else {
        input.type = "password";
        element.textContent = "👁️"; 
    }
}

// Show/Hide sections
function showRegister() {
  document.querySelector('section.card.auth').style.display = "none";
  document.getElementById("registerSection").style.display = "block";
}
function showLogin() {
  document.getElementById("registerSection").style.display = "none";
  document.querySelector('section.card.auth').style.display = "block";
}

// ===== REGISTER =====
document.getElementById("regBtn").addEventListener("click", () => {
  const user = document.getElementById("regUser").value.trim();
  const pass = document.getElementById("regPass").value.trim();
  if(!user || !pass) return alert("Fill all fields");
  if(localStorage.getItem("user_" + user)) return alert("User already exists!");

  localStorage.setItem("user_" + user, JSON.stringify({
    username: user,
    password: pass,
    created: new Date().toLocaleString()
  }));
  localStorage.setItem("reports_" + user, JSON.stringify([])); // per-user reports
  alert("Account created! You may now login.");
  showLogin();
});

// ===== LOGIN =====
document.getElementById("loginBtn").addEventListener("click", () => {
  const user = document.getElementById("loginUser").value.trim();
  const pass = document.getElementById("loginPass").value.trim();

  const account = localStorage.getItem("user_" + user);
  if(!account) return alert("You need to register first before logging in.");

  const data = JSON.parse(account);
  if(data.password !== pass) return alert("Incorrect password!");

  localStorage.setItem("activeUser", user);

  if(!localStorage.getItem("reports_" + user)) {
    localStorage.setItem("reports_" + user, JSON.stringify([]));
  }
  window.location.href = "dashboard.php";
});