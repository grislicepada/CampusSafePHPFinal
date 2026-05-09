<?php
session_start();
require_once 'db_conn.php'; // Includes $pdo

// Initialize variables
 $error_msg = "";
 $email_input = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email_input = $_POST['email'] ?? ''; // Save email to remember it in the form
    $password = $_POST['password'] ?? '';

    if (empty($email_input) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // SECURE PDO QUERY
        $sql = "SELECT * FROM tbl_users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email_input]);
        
        $row = $stmt->fetch();

        if ($row) {
            // NOTE: If your passwords in the database are STILL PLAIN TEXT, 
            // change: password_verify($password, $row['password'])
            // to:     $password === $row['password']
            if (password_verify($password, $row['password'])) {
                
                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = $row['role']; 

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();

            } else {
                $error_msg = "Incorrect password.";
            }
        } else {
            $error_msg = "No account found with that email.";
        }
    }
}

// If user is already logged in, redirect them to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusSafe - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-container">
        <h2>CampusSafe Login</h2>

        <!-- Display Error Message if any -->
        <?php if (!empty($error_msg)): ?>
            <div class="error-message" style="color: red; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Form submits to itself -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email_input); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

</body>
</html>