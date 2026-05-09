<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "campussafe";

// Pag-create sa connection
$conn = mysqli_connect($host, $user, $password, $database);

// Imbis echo, check lang nato kung naay error
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>