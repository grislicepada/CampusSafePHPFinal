<?php
session_start();
require_once 'db_conn.php';

// Protect the page
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- HANDLE UPDATE PROFILE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
  $new_name = trim($_POST['name']);
  $new_email = trim($_POST['email']);

  if (!empty($new_name) && !empty($new_email)) {
    try {
      // Check if email is already taken by another user
      $check_sql = "SELECT user_id FROM tbl_users WHERE email = :email AND user_id != :user_id";
      $check_stmt = $pdo->prepare($check_sql);
      $check_stmt->execute(['email' => $new_email, 'user_id' => $user_id]);

      if ($check_stmt->fetch()) {
        $message = "Email is already taken by another account.";
      } else {
        $update_sql = "UPDATE tbl_users SET name = :name, email = :email WHERE user_id = :user_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute(['name' => $new_name, 'email' => $new_email, 'user_id' => $user_id]);

        // Update the session name so the dashboard shows the new name immediately
        $_SESSION['name'] = $new_name;
        $message = "Profile updated successfully!";
      }
    } catch (PDOException $e) {
      $message = "Error updating profile.";
    }
  } else {
    $message = "Name and Email cannot be empty.";
  }
}

// --- HANDLE EXPORT REPORTS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'export') {
  // Fetch all reports for this user to export
  $export_sql = "SELECT category, description, status, date_reported FROM tbl_reports WHERE user_id = :user_id ORDER BY date_reported DESC";
  $export_stmt = $pdo->prepare($export_sql);
  $export_stmt->execute(['user_id' => $user_id]);
  $reports_data = $export_stmt->fetchAll(PDO::FETCH_ASSOC);

  // Send headers to download JSON file
  header('Content-Type: application/json');
  header('Content-Disposition: attachment; filename="CampusSafe_Reports.json"');
  echo json_encode($reports_data, JSON_PRETTY_PRINT);
  exit(); // Stop executing the rest of the page
}

// --- FETCH CURRENT USER DATA ---
$sql = "SELECT * FROM tbl_users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampusSafe - Profile</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--color-border);
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: var(--color-primary);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 2.5rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .profile-info h3 {
      margin: 0;
      color: var(--color-secondary);
      font-size: 1.5rem;
    }

    .profile-info p {
      margin: 5px 0 0;
      color: var(--color-subtext);
    }

    .profile-form {
      display: grid;
      gap: 15px;
      margin-top: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--color-text);
      font-size: 0.9em;
    }

    .form-group input {
      padding: 12px;
      border: 1px solid var(--color-border);
      border-radius: 6px;
      font-size: 1em;
    }

    .form-group input:focus {
      border-color: var(--color-primary);
      outline: none;
    }

    .profile-actions {
      margin-top: 25px;
      display: flex;
      gap: 10px;
    }

    .btn-save {
      padding: 12px 20px;
      background-color: var(--color-primary);
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-save:hover {
      background-color: #008D79;
    }

    .btn-export {
      padding: 12px 20px;
      background-color: var(--color-warning);
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-export:hover {
      background-color: #E68900;
    }

    .notif {
      position: fixed;
      top: 80px;
      right: 20px;
      padding: 15px 25px;
      background: #00796B;
      color: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      font-weight: 600;
      z-index: 1000;
      animation: slideIn 0.3s ease-out, fadeOut 0.5s ease-in 2.5s forwards;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-20px);
      }
    }
  </style>
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
      <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </header>

  <main class="container">
    <div class="card">
      <h2>My Profile</h2>

      <!-- Profile Avatar & Basic Info -->
      <div class="profile-header">
        <div class="profile-avatar">
          <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <div class="profile-info">
          <h3><?php echo htmlspecialchars($user['name']); ?></h3>
          <p><?php echo htmlspecialchars($user['email']); ?></p>
          <p style="font-size: 0.85em; margin-top: 2px;">Role:
            <strong><?php echo htmlspecialchars($user['role']); ?></strong></p>
        </div>
      </div>

      <!-- Edit Profile Form -->
      <form action="profile.php" method="POST" class="profile-form">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
          <label>Member Since</label>
          <input type="text" value="<?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?>" disabled
            style="background: #f9f9f9; color: #666;">
        </div>

        <div class="profile-actions">
          <button type="submit" class="btn-save">Save Changes</button>

          <!-- Export Button triggers a separate form submission via JS -->
          <button type="button" onclick="document.getElementById('exportForm').submit();" class="btn-export">Export
            Reports (JSON)</button>
        </div>
      </form>

      <!-- Hidden form for Export Action -->
      <form id="exportForm" method="POST" action="profile.php" style="display:none;">
        <input type="hidden" name="action" value="export">
      </form>

    </div>
  </main>

  <!-- Floating Notification -->
  <?php if (!empty($message)): ?>
    <div class="notif"><?php echo $message; ?></div>
  <?php endif; ?>

  <script src="main.js"></script>
</body>

</html>