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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ADD SUPPLIER
    if ($action == 'add') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        // Validation
        if (empty($name) || empty($phone) || empty($address)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        // Validate phone number (10 digits)
        $phoneDigits = preg_replace('/\D/', '', $phone);
        if (!preg_match('/^[0-9]{10}$/', $phoneDigits)) {
            echo json_encode(['success' => false, 'message' => 'Phone number must contain exactly 10 digits']);
            exit();
        }
        
        // Validate name length
        if (strlen($name) < 3 || strlen($name) > 50) {
            echo json_encode(['success' => false, 'message' => 'Supplier name must be between 3 and 50 characters']);
            exit();
        }
        
        // Validate address length
        if (strlen($address) < 5 || strlen($address) > 100) {
            echo json_encode(['success' => false, 'message' => 'Address must be between 5 and 100 characters']);
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO supplier (name, phone_no, address) VALUES (?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("sss", $name, $phoneDigits, $address);
        
        if ($stmt->execute()) {
            $sup_id = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Supplier added successfully',
                'supplier_id' => $sup_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding supplier: ' . $stmt->error]);
        }
        $stmt->close();
    }
    
    // UPDATE SUPPLIER
    elseif ($action == 'update') {
        $sup_id = intval($_POST['sup_id']);
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        // Validation
        if (empty($name) || empty($phone) || empty($address)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        // Validate phone number
        $phoneDigits = preg_replace('/\D/', '', $phone);
        if (!preg_match('/^[0-9]{10}$/', $phoneDigits)) {
            echo json_encode(['success' => false, 'message' => 'Phone number must contain exactly 10 digits']);
            exit();
        }
        
        // Validate name length
        if (strlen($name) < 3 || strlen($name) > 50) {
            echo json_encode(['success' => false, 'message' => 'Supplier name must be between 3 and 50 characters']);
            exit();
        }
        
        // Validate address length
        if (strlen($address) < 5 || strlen($address) > 100) {
            echo json_encode(['success' => false, 'message' => 'Address must be between 5 and 100 characters']);
            exit();
        }
        
        // Check if supplier exists
        $checkStmt = $conn->prepare("SELECT sup_id FROM supplier WHERE sup_id = ?");
        if (!$checkStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $checkStmt->bind_param("i", $sup_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Supplier not found']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        $stmt = $conn->prepare("UPDATE supplier SET name = ?, phone_no = ?, address = ? WHERE sup_id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("sssi", $name, $phoneDigits, $address, $sup_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating supplier: ' . $stmt->error]);
        }
        $stmt->close();
    }
    
    // DELETE SUPPLIER
    elseif ($action == 'delete') {
        $sup_id = intval($_POST['sup_id']);
        
        // Check if supplier exists
        $checkStmt = $conn->prepare("SELECT sup_id FROM supplier WHERE sup_id = ?");
        if (!$checkStmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $checkStmt->bind_param("i", $sup_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Supplier not found']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        // Check if supplier has associated purchase orders
        $orderCheck = $conn->prepare("SELECT COUNT(*) as count FROM purchase_order WHERE sup_id = ?");
        if (!$orderCheck) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $orderCheck->bind_param("i", $sup_id);
        $orderCheck->execute();
        $orderResult = $orderCheck->get_result();
        $orderRow = $orderResult->fetch_assoc();
        
        if ($orderRow['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete supplier with active purchase orders']);
            $orderCheck->close();
            exit();
        }
        $orderCheck->close();
        
        // Check if supplier has supplies relationships
        $supplyCheck = $conn->prepare("SELECT COUNT(*) as count FROM supplies WHERE sup_id = ?");
        if (!$supplyCheck) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $supplyCheck->bind_param("i", $sup_id);
        $supplyCheck->execute();
        $supplyResult = $supplyCheck->get_result();
        $supplyRow = $supplyResult->fetch_assoc();
        $supplyCheck->close();
        
        // Delete from supplies table first (due to foreign key)
        if ($supplyRow['count'] > 0) {
            $deleteSupply = $conn->prepare("DELETE FROM supplies WHERE sup_id = ?");
            if (!$deleteSupply) {
                echo json_encode(['success' => false, 'message' => 'Database error during cleanup']);
                exit();
            }
            
            $deleteSupply->bind_param("i", $sup_id);
            if (!$deleteSupply->execute()) {
                echo json_encode(['success' => false, 'message' => 'Error deleting supplier relationships']);
                $deleteSupply->close();
                exit();
            }
            $deleteSupply->close();
        }
        
        // Delete the supplier
        $stmt = $conn->prepare("DELETE FROM supplier WHERE sup_id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $stmt->bind_param("i", $sup_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting supplier: ' . $stmt->error]);
        }
        $stmt->close();
    }
    
    // SEARCH SUPPLIER
    elseif ($action == 'search') {
        $search = trim($_POST['search']);
        
        if (strlen($search) < 1) {
            echo json_encode(['success' => false, 'message' => 'Search term cannot be empty']);
            exit();
        }
        
        if (strlen($search) > 100) {
            echo json_encode(['success' => false, 'message' => 'Search term is too long']);
            exit();
        }
        
        $stmt = $conn->prepare("SELECT sup_id, name, phone_no, address FROM supplier WHERE name LIKE ? OR phone_no LIKE ? OR address LIKE ? ORDER BY name ASC LIMIT 100");
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = [
                'sup_id' => htmlspecialchars($row['sup_id']),
                'name' => htmlspecialchars($row['name']),
                'phone_no' => htmlspecialchars($row['phone_no']),
                'address' => htmlspecialchars($row['address'])
            ];
        }
        
        echo json_encode(['success' => true, 'suppliers' => $suppliers]);
        $stmt->close();
    }
    
    exit();
}

// GET ALL SUPPLIERS (for initial page load)
$result = $conn->query("SELECT sup_id, name, phone_no, address FROM supplier ORDER BY name ASC");
$suppliers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
} else {
    $error_message = "Error fetching suppliers: " . $conn->error;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/suppliers.css">
    <title>StockMaster - Suppliers Management</title>
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
            <div class="menu-item active">
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
            <?php if (isset($error_message)): ?>
            <div class="toast toast-error show">
                <div class="toast-icon">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
                <span class="toast-message"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <div class="search-header">
                <div class="search-box">
                    <input type="text" placeholder="Search supplier by name, phone or address" id="supplierSearch">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
                <button class="add-btn">
                    <span>+</span>
                    <span>Add supplier</span>
                </button>
            </div>

            <div class="table-container">
                <table id="supplierTable">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Supplier ID</th>
                            <th>Phone No</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($suppliers) > 0): ?>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['sup_id']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone_no']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['address']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn delete-btn" title="Delete supplier">
                                            <svg fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                        </button>
                                        <button class="action-btn edit-btn" title="Edit supplier">
                                            <svg fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999; padding: 40px;">
                                    No suppliers found. Click "Add supplier" to create one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-suppliers.js"></script>
</body>
</html>