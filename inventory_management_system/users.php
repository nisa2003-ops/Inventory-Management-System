<?php
session_start();
include("config/db.php");

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if user is Manager or Admin
if ($_SESSION['user_role'] !== 'Manager' && $_SESSION['user_role'] !== 'Admin') {
    echo "Access denied. You don't have permission to access this page.";
    exit();
}

$error = "";
$success = "";
$edit_user_data = null;

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username already exists
        $check_sql = "SELECT user_id FROM user WHERE username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Insert new user

            
            $insert_sql = "INSERT INTO user (username, user_password, user_role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $username, $password, $role);
            
            if ($stmt->execute()) {
                $success = "User added successfully!";
            } else {
                $error = "Failed to add user: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['username']);
    $u_password = $_POST['password'];
    $u_role = $_POST['role'];
    
    // Validation
    if (empty($name) || empty($u_role)) {
        $error = "Username and role are required";
    } else {
        // Check if username already exists (excluding current user)
        $check_sql = "SELECT user_id FROM user WHERE username = ? AND user_id != ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Update user
            if (!empty($u_password)) {
                // Hash the password before storing
                
                $update_sql = "UPDATE user SET username = ?, user_password = ?, user_role = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("sssi", $name, $u_password, $u_role, $user_id);
            } else {
                // Update without changing password
                $update_sql = "UPDATE user SET username = ?, user_role = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ssi", $name, $u_role, $user_id);
            }
            
            if ($stmt->execute()) {
                $success = "User updated successfully!";
                // Redirect to prevent form resubmission
                header("Location: users.php?success=1");
                exit();
            } else {
                $error = "Failed to update user: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prevent deleting yourself
    if ($delete_id !== $_SESSION['user_id']) {
        $delete_sql = "DELETE FROM user WHERE user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user";
        }
        $stmt->close();
    } else {
        $error = "You cannot delete yourself";
    }
}

// Fetch all users from database
$sql = "SELECT * FROM user ORDER BY user_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster - Users</title>
    <link rel="stylesheet" href="assets/css/users.css">
</head>

<body>
    <div class="sidebar">
        <div class="logo">StockMaster</div>
        <div class="menu">
            <div class="menu-item" onclick="location.href='manager-dashboard.php'">
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
            <div class="menu-item active">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
                <span>Users</span>
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
            <div class="menu-item">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                </svg>
                <span>Settings</span>
            </div>
        </div>
        <div class="logout" onclick="location.href='logout.php'">
            <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Log out</span>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="search-box">
                <input type="text" placeholder="Search user" id="searchInput" onkeyup="searchUsers()">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </div>
            <button class="add-user-btn" onclick="openAddModal()">
                <span>+</span>
                <span>Add user</span>
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <div class="table-container">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo strtolower($row['user_role']); ?>">
                                            <?php echo htmlspecialchars($row['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="action-btn delete-btn" 
                                                    onclick="confirmDelete(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')"
                                                    <?php echo ($row['user_id'] == $_SESSION['user_id']) ? 'disabled title="Cannot delete yourself"' : ''; ?>>
                                                <svg fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                                </svg>
                                            </button>
                                            <button class="action-btn edit-btn" 
                                                    onclick='openEditModal(<?php echo json_encode($row); ?>)'>
                                                <svg fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px;">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST" action="" onsubmit="return validateAddForm()">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_username">Username *</label>
                        <input type="text" id="add_username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="add_password">Password *</label>
                        <input type="password" id="add_password" name="password" required minlength="6" 
                               placeholder="Minimum 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="add_confirm_password">Confirm Password *</label>
                        <input type="password" id="add_confirm_password" name="confirm_password" required 
                               minlength="6" placeholder="Re-enter password">
                    </div>

                    <div class="form-group">
                        <label for="add_role">Role *</label>
                        <select id="add_role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_username">Username *</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_password">New Password</label>
                        <input type="password" id="edit_password" name="password" 
                               minlength="6" placeholder="Leave blank to keep current password">
                        <small>Leave blank if you don't want to change the password</small>
                    </div>

                    <div class="form-group">
                        <label for="edit_role">Role *</label>
                        <select id="edit_role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/users.js"></script>
    
</body>

</html>