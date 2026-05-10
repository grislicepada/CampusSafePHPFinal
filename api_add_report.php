<?php
session_start();
require_once 'db_conn.php';

// Tell the browser we are sending back JSON data
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    if (!empty($category) && !empty($description)) {
        try {
            // Insert the report with the exact latitude and longitude from the map click!
            $sql = "INSERT INTO tbl_reports (user_id, location_id, category, description, latitude, longitude) 
                    VALUES (:user_id, 1, :category, :description, :lat, :lng)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'category' => $category,
                'description' => $description,
                'lat' => $lat,
                'lng' => $lng
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Report added successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Type and Description are required.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>