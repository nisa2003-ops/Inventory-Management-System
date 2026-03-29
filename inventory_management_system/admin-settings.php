<?php
session_start();

// Database connection
$conn = new mysqli("127.0.0.1", "root", "", "inventory_store");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = null;
$error_message = '';
$success_message = '';

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'update') {
        $new_username = trim($_POST['username']);
        $new_password = trim($_POST['password']);
        
        // Validation
        if (empty($new_username)) {
            echo json_encode(['success' => false, 'message' => 'Username cannot be empty']);
            exit();
        }
        
        if (strlen($new_username) < 3 || strlen($new_username) > 50) {
            echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
            exit();
        }
        
        // Check if username already exists (excluding current user)
        $checkStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? AND user_id != ?");
        if (!$checkStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $checkStmt->bind_param("si", $new_username, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        // Update username
        $updateStmt = $conn->prepare("UPDATE user SET username = ? WHERE user_id = ?");
        if (!$updateStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $updateStmt->bind_param("si", $new_username, $user_id);
        
        if (!$updateStmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error updating username']);
            $updateStmt->close();
            exit();
        }
        $updateStmt->close();
        
        // Update password if provided
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit();
            }
            
            
            $passStmt = $conn->prepare("UPDATE user SET user_password = ? WHERE user_id = ?");
            
            if (!$passStmt) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit();
            }
            
            $passStmt->bind_param("si", $new_password, $user_id);
            
            if (!$passStmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Error updating password']);
                $passStmt->close();
                exit();
            }
            $passStmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'User details updated successfully',
            'username' => $new_username
        ]);
        exit();
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT user_id, username, user_role FROM user WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        $error_message = "User not found";
    }
    $stmt->close();
} else {
    $error_message = "Database error: " . $conn->error;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster - Settings</title>
    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">StockMaster</div>
        <div class="menu">
            <div class="menu-item">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span>Dashboard</span>
            </div>
            <div class="menu-item">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-5 12H9v-2h6v2zm5-7H4V4h16v3z"/>
                </svg>
                <span>Inventory</span>
            </div>
            <div class="menu-item">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
                <span>Suppliers</span>
            </div>
            <div class="menu-item">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
                <span>Customers</span>
            </div>
            <div class="menu-item active">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                </svg>
                <span>Settings</span>
            </div>
        </div>
        <div class="logout">
            <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Log out</span>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            Good morning
        </div>

        <div class="content">
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php elseif ($user_data): ?>
                <div class="profile-card">
                    <div class="avatar">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>

                    <div class="user-info">
                        <div class="info-row">
                            <span class="info-label">User ID :</span>
                            <span class="info-value" id="userId"><?php echo htmlspecialchars($user_data['user_id']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">User name :</span>
                            <span class="info-value" id="userName"><?php echo htmlspecialchars($user_data['username']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Role :</span>
                            <span class="info-value" id="userRole"><?php echo htmlspecialchars($user_data['user_role']); ?></span>
                        </div>
                    </div>

                    <button class="edit-btn">Edit</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/admin-settings.js"></script>
</body>
</html>