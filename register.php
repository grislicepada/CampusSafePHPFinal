<?php
session_start();
require_once 'db_conn.php'; // Includes $pdo

// Initialize variables
 $error_msg = "";
 $show_success_modal = false;
 $email_input = "";

// If user is already logged in, redirect them to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email_input = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($email_input) || empty($password) || empty($confirm_password)) {
        $error_msg = "Please fill in all fields.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM tbl_users WHERE email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(['email' => $email_input]);

        if ($check_stmt->fetch()) {
            $error_msg = "An account with this email already exists.";
        } else {
            // SECURE PASSWORD HASHING
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Default role for new users
            $role = 'user'; 

            // INSERT NEW USER INTO DATABASE
            $insert_sql = "INSERT INTO tbl_users (name, email, password, role) VALUES (:name, :email, :password, :role)";
            $insert_stmt = $pdo->prepare($insert_sql);
            
            try {
                $insert_stmt->execute([
                    'name' => explode('@', $email_input)[0],
                    'email' => $email_input,
                    'password' => $hashed_password,
                    'role' => $role
                ]);

                // Success! Trigger the modal instead of a div message
                $show_success_modal = true;
                $email_input = ""; // Clear input

            } catch (PDOException $e) {
                $error_msg = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusSafe - Register</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <div class="login-container">
        <h2>Create Account</h2>

        <!-- Display Error Message if any -->
        <?php if (!empty($error_msg)): ?>
            <div class="error-message">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Form submits to itself -->
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email_input); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit" class="btn-login">Register</button>
        </form>

        <!-- Login Link -->
        <div class="register-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <?php if ($show_success_modal): ?>
    <div id="successModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon">✓</div>
            <h3>Registration Successful!</h3>
            <p>Your account has been created. Redirecting to login...</p>
        </div>
    </div>

    <script>
        // Redirect to login page after 3 seconds (3000 milliseconds)
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
    </script>
    <?php endif; ?>

</body>
</html>