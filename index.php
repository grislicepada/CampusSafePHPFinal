<?php
session_start();

// I-include ang database connection
require_once 'db_conn.php';

// Check kung active ang session
if (!isset($_SESSION['user_id'])) {
    // Wala pay session, padala sa login
    header("Location: login.php");
    exit();
} else {
    // Naay session, padala sa dashboard
    header("Location: dashboard.php");
    exit();
}
?>