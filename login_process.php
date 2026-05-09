<?php
session_start();
include 'db_conn.php';

// Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Use ?? to prevent the "Undefined array key" warning if fields are missing
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // SECURE QUERY: Prevents SQL Injection using prepared statements
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);

        // NOTE: This checks plain text passwords. 
        // It is highly recommended to use password_hash() on registration 
        // and password_verify() here instead of == or ===
        if($password === $row['password']){
            
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role']; // Good to save role too!

            header("Location: dashboard.php");
            exit();

        } else {
            echo "Incorrect Password";
        }

    } else {
        echo "User Not Found";
    }

} else {
    // If someone tries to visit login_process.php directly without clicking login
    header("Location: login.php");
    exit();
}
?>