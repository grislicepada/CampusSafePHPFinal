<?php
 $host = "localhost";
 $user = "root";
 $password = "";
 $database = "campussafe";

try {
    // PDO Connection String
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    
    // PDO Options: Error mode as Exception, Fetch as Associative Array
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>