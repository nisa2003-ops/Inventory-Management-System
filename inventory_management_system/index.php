<?php
session_start();
include("config/db.php");

$error = "";

// LOGIN VERIFICATION CODE GOES HERE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND user_password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_role'] = $row['user_role'];

        // Redirect based on role
        if ($row['user_role'] === 'Manager') {
            header("Location: manager-dashboard.php");
            exit();
        } elseif ($row['user_role'] === 'Admin') {
            header("Location: admin-dashboard.php");
            exit();
        } else {
            header("Location: cashier-dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid Username or Password";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <!-- Decorative shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
    <div class="shape shape-6"></div>
    <div class="shape shape-7"></div>

    <!-- Login form -->
    <div class="login-container">
        <h1>Login</h1>

        <form method="POST" action="">
            <div class="input-group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path
                            d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" />
                    </svg>
                <input type="password" name="password" placeholder="Password" required>
            </div> 

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
    
</body>

</html>