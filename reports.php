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

// --- HANDLE POST ACTIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $category = trim($_POST['category']);
            $description = trim($_POST['description']);

            if (!empty($category) && !empty($description)) {
                // location_id is set to 1 by default since it's not in the form
                $sql = "INSERT INTO tbl_reports (user_id, location_id, category, description) VALUES (:user_id, 1, :category, :description)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['user_id' => $user_id, 'category' => $category, 'description' => $description]);
                $message = "Report added successfully!";
            } else {
                $message = "Both fields are required.";
            }

        } elseif ($action === 'delete') {
            $report_id = $_POST['report_id'];
            $sql = "DELETE FROM tbl_reports WHERE report_id = :report_id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['report_id' => $report_id, 'user_id' => $user_id]);
            $message = "Report deleted.";

        } elseif ($action === 'done') {
            $report_id = $_POST['report_id'];
            $sql = "UPDATE tbl_reports SET status = 'Completed' WHERE report_id = :report_id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['report_id' => $report_id, 'user_id' => $user_id]);
            $message = "Report marked as done.";
        }
    } catch (PDOException $e) {
        $message = "Database error occurred.";
    }

    // Redirect to avoid form resubmission
    header("Location: reports.php?msg=" . urlencode($message));
    exit();
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// --- FETCH REPORTS ---
$sql = "SELECT * FROM tbl_reports WHERE user_id = :user_id ORDER BY date_reported DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CampusSafe - Reports</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Floating Notification Style */
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
            <h2>Reports (CRUD)</h2>
            <div class="row">
                <input id="reportType" type="text" placeholder="Type (e.g., Flood, Fire, Theft)">
                <input id="reportDesc" type="text" placeholder="Short description">
                <button id="addReportBtn">Add Report</button>
            </div>

            <table id="reportsTable" class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['date_reported'])); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="done">
                                            <input type="hidden" name="report_id" value="<?php echo $row['report_id']; ?>">
                                            <button type="submit" class="btn-done">Done</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn-done" disabled>Completed</button>
                                    <?php endif; ?>

                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete this report?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="report_id" value="<?php echo $row['report_id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:#888;">No reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Floating Notification -->
    <?php if (!empty($message)): ?>
        <div class="notif"><?php echo $message; ?></div>
    <?php endif; ?>

    <script src="main.js"></script>

    <!-- Replaced reports.js with inline script to submit to PHP -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const addBtn = document.getElementById("addReportBtn");

            if (addBtn) {
                addBtn.addEventListener("click", function () {
                    const category = document.getElementById("reportType").value.trim();
                    const description = document.getElementById("reportDesc").value.trim();

                    if (!category || !description) {
                        alert("Please fill in both Type and Description.");
                        return;
                    }

                    // Create hidden form to submit data to PHP
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "reports.php";

                    const fields = { action: 'add', category: category, description: description };

                    for (const key in fields) {
                        const hiddenField = document.createElement("input");
                        hiddenField.type = "hidden";
                        hiddenField.name = key;
                        hiddenField.value = fields[key];
                        form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                });
            }
        });
    </script>

</body>

</html>