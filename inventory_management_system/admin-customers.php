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

// Handle customer addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists
        $check_sql = "SELECT customer_id FROM customers WHERE customer_email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Insert new customer
            $insert_sql = "INSERT INTO customers (customer_name, customer_email, customer_phone) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $name, $email, $phone);
            
            if ($stmt->execute()) {
                $success = "Customer added successfully!";
            } else {
                $error = "Failed to add customer: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle customer update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $customer_id = intval($_POST['customer_id']);
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists (excluding current customer)
        $check_sql = "SELECT customer_id FROM customers WHERE customer_email = ? AND customer_id != ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("si", $email, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Update customer
            $update_sql = "UPDATE customers SET customer_name = ?, customer_email = ?, customer_phone = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssi", $name, $email, $phone, $customer_id);
            
            if ($stmt->execute()) {
                $success = "Customer updated successfully!";
            } else {
                $error = "Failed to update customer: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle customer deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $delete_sql = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $success = "Customer deleted successfully!";
    } else {
        $error = "Failed to delete customer";
    }
    $stmt->close();
}

// Fetch all customers from database
$sql = "SELECT * FROM customers ORDER BY customer_id DESC";
$result = $conn->query($sql);

// Fetch all sales orders with customer information
$sales_sql = "SELECT 
                so.order_id, 
                so.customer_id, 
                so.user_id, 
                so.sales_date,
                c.customer_name,
                SUM(si.quantity) as total_items,
                SUM(si.total) as order_total
              FROM sales_order so
              LEFT JOIN customers c ON so.customer_id = c.customer_id
              LEFT JOIN sales_item si ON so.order_id = si.order_id
              GROUP BY so.order_id, so.customer_id, so.user_id, so.sales_date, c.customer_name
              ORDER BY so.order_id DESC";
$sales_result = $conn->query($sales_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster - Customers</title>
    <link rel="stylesheet" href="assets/css/customers.css">
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
            <div class="menu-item active">
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
        <div class="logout">
            <svg fill="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Log out</span>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            Welcome, Manager
        </div>

        <div class="content">
            <!-- Customers Section -->
            <div class="section">
                <div class="section-header">
                    <div class="search-box">
                        <input type="text" placeholder="Search customer" id="customerSearch" onkeyup="searchTable('customerTable')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </div>
                    <button class="add-btn" id="addUserBtn" onclick="openAddCustomerModal()">
                        <span>+</span>
                        <span>Add customer</span>
                    </button>
                </div>

                <?php if ($success): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table id="customerTable">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Customer ID</th>
                                <th>Email</th>
                                <th>Phone No</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td>CU<?php echo str_pad($row['customer_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                        <td>
                                            <div class="actions">
                                                <button class="action-btn delete-btn" 
                                                        onclick="confirmDeleteCustomer(<?php echo $row['customer_id']; ?>, '<?php echo htmlspecialchars($row['customer_name']); ?>')">
                                                    <svg fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                                    </svg>
                                                </button>
                                                <button class="action-btn edit-btn" 
                                                        onclick='openEditCustomerModal(<?php echo json_encode($row); ?>)'>
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
                                    <td colspan="5" style="text-align: center; padding: 30px;">No customers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sales Order Section -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">Sales Orders</div>
                    <div class="search-box">
                        <input type="text" placeholder="Search sales order" id="salesSearch" onkeyup="searchTable('salesTable')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </div>
                </div>

                <div class="table-container">
                    <table id="salesTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Sales Date</th>
                                <th>Items</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sales_result && $sales_result->num_rows > 0): ?>
                                <?php while($row = $sales_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>OR<?php echo str_pad($row['order_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                        <td><?php echo htmlspecialchars($row['sales_date']); ?></td>
                                        <td><?php echo intval($row['total_items']); ?></td>
                                        <td>Rs. <?php echo number_format($row['order_total'] ?? 0, 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">No sales orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Customer</h2>
                <span class="modal-close" onclick="closeAddCustomerModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_customer_name">Customer Name *</label>
                        <input type="text" id="add_customer_name" name="customer_name" required placeholder="Enter full name">
                    </div>

                    <div class="form-group">
                        <label for="add_email">Email *</label>
                        <input type="email" id="add_email" name="email" required placeholder="Enter email address">
                    </div>

                    <div class="form-group">
                        <label for="add_phone">Phone Number *</label>
                        <input type="tel" id="add_phone" name="phone" required placeholder="Enter phone number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddCustomerModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <span class="modal-close" onclick="closeEditCustomerModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_customer_id" name="customer_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_customer_name">Customer Name *</label>
                        <input type="text" id="edit_customer_name" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_phone">Phone Number *</label>
                        <input type="tel" id="edit_phone" name="phone" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditCustomerModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin-customers.js"></script>

</body>

</html>