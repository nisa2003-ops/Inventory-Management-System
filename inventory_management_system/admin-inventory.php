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

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $product_id = trim($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $category_id = trim($_POST['category_id']);
    $user_id = $_SESSION['user_id'];
    $price = trim($_POST['price']);
    $unit = trim($_POST['unit']);
    $current_stock = trim($_POST['current_stock']);
    
    // Validation
    if (empty($product_id) || empty($product_name) || empty($category_id) || empty($price) || empty($unit) || empty($current_stock)) {
        $error = "All fields are required";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Invalid price";
    } elseif (!is_numeric($current_stock) || $current_stock < 0) {
        $error = "Invalid stock quantity";
    } else {
        // Check if product ID already exists
        $check_sql = "SELECT product_id FROM product WHERE product_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Product ID already exists";
        } else {
            // Insert new product
            $insert_sql = "INSERT INTO product (product_id, category_id, user_id, product_name, price, unit, current_stock) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssissss", $product_id, $category_id, $user_id, $product_name, $price, $unit, $current_stock);
            
            if ($stmt->execute()) {
                $success = "Product added successfully!";
            } else {
                $error = "Failed to add product: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $product_id = trim($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $category_id = trim($_POST['category_id']);
    $price = trim($_POST['price']);
    $unit = trim($_POST['unit']);
    $current_stock = trim($_POST['current_stock']);
    
    // Validation
    if (empty($product_name) || empty($category_id) || empty($price) || empty($unit) || empty($current_stock)) {
        $error = "All fields are required";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Invalid price";
    } elseif (!is_numeric($current_stock) || $current_stock < 0) {
        $error = "Invalid stock quantity";
    } else {
        // Update product
        $update_sql = "UPDATE product SET product_name = ?, category_id = ?, price = ?, unit = ?, current_stock = ? WHERE product_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssss", $product_name, $category_id, $price, $unit, $current_stock, $product_id);
        
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
        } else {
            $error = "Failed to update product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = trim($_GET['delete_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, delete from supplies table (child table)
        $delete_supplies_sql = "DELETE FROM supplies WHERE product_id = ?";
        $stmt1 = $conn->prepare($delete_supplies_sql);
        $stmt1->bind_param("s", $delete_id);
        $stmt1->execute();
        $stmt1->close();
        
        // Then, delete from purchase_order table (if exists)
        $delete_purchase_sql = "DELETE FROM purchase_order WHERE product_id = ?";
        $stmt2 = $conn->prepare($delete_purchase_sql);
        $stmt2->bind_param("s", $delete_id);
        $stmt2->execute();
        $stmt2->close();
        
        // Finally, delete the product
        $delete_product_sql = "DELETE FROM product WHERE product_id = ?";
        $stmt3 = $conn->prepare($delete_product_sql);
        $stmt3->bind_param("s", $delete_id);
        $stmt3->execute();
        $stmt3->close();
        
        // Commit transaction
        $conn->commit();
        $success = "Product and all related records deleted successfully!";
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error = "Failed to delete product: " . $e->getMessage();
    }
}

// Handle purchase order addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_purchase') {
    $sup_id = trim($_POST['sup_id']);
    $product_id = trim($_POST['product_id']);
    $order_date = trim($_POST['order_date']);
    $exp_date = trim($_POST['exp_date']);
    $total_amount = trim($_POST['total_amount']);
    
    // Validation
    if (empty($sup_id) || empty($product_id) || empty($order_date) || empty($total_amount)) {
        $error = "All fields are required for purchase order";
    } elseif (!is_numeric($total_amount) || $total_amount <= 0) {
        $error = "Invalid total amount";
    } else {
        // Check if supplier exists
        $check_sup = "SELECT sup_id FROM supplier WHERE sup_id = ?";
        $stmt = $conn->prepare($check_sup);
        $stmt->bind_param("i", $sup_id);
        $stmt->execute();
        $sup_result = $stmt->get_result();
        
        // Check if product exists
        $check_prod = "SELECT product_id FROM product WHERE product_id = ?";
        $stmt2 = $conn->prepare($check_prod);
        $stmt2->bind_param("s", $product_id);
        $stmt2->execute();
        $prod_result = $stmt2->get_result();
        
        if ($sup_result->num_rows == 0) {
            $error = "Supplier ID does not exist";
        } elseif ($prod_result->num_rows == 0) {
            $error = "Product ID does not exist";
        } else {
            // Insert purchase order
            $insert_sql = "INSERT INTO purchase_order (sup_id, product_id, order_date, exp_date, total_amount) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isssd", $sup_id, $product_id, $order_date, $exp_date, $total_amount);
            
            if ($stmt->execute()) {
                // Also insert into supplies table
                $supplies_sql = "INSERT INTO supplies (sup_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE sup_id = sup_id";
                $stmt_supplies = $conn->prepare($supplies_sql);
                $stmt_supplies->bind_param("is", $sup_id, $product_id);
                $stmt_supplies->execute();
                $stmt_supplies->close();
                
                $success = "Purchase order added successfully!";
            } else {
                $error = "Failed to add purchase order: " . $conn->error;
            }
        }
        $stmt->close();
        $stmt2->close();
    }
}

// Handle purchase order update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_purchase') {
    $purchase_id = trim($_POST['purchase_id']);
    $sup_id = trim($_POST['sup_id']);
    $product_id = trim($_POST['product_id']);
    $order_date = trim($_POST['order_date']);
    $exp_date = trim($_POST['exp_date']);
    $total_amount = trim($_POST['total_amount']);
    
    // Validation
    if (empty($sup_id) || empty($product_id) || empty($order_date) || empty($exp_date) || empty($total_amount)) {
        $error = "All fields are required";
    } elseif (!is_numeric($total_amount) || $total_amount <= 0) {
        $error = "Invalid total amount";
    } else {
        // Update purchase order
        $update_sql = "UPDATE purchase_order SET sup_id = ?, product_id = ?, order_date = ?, exp_date = ?, total_amount = ? WHERE purchase_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("isssdi", $sup_id, $product_id, $order_date, $exp_date, $total_amount, $purchase_id);
        
        if ($stmt->execute()) {
            // Update supplies table
            $supplies_sql = "INSERT INTO supplies (sup_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE sup_id = sup_id";
            $stmt_supplies = $conn->prepare($supplies_sql);
            $stmt_supplies->bind_param("is", $sup_id, $product_id);
            $stmt_supplies->execute();
            $stmt_supplies->close();
            
            $success = "Purchase order updated successfully!";
        } else {
            $error = "Failed to update purchase order: " . $conn->error;
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_purchase_id'])) {
    $delete_purchase_id = trim($_GET['delete_purchase_id']);
    
    $delete_sql = "DELETE FROM purchase_order WHERE purchase_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_purchase_id);
    
    if ($stmt->execute()) {
        $success = "Purchase order deleted successfully!";
    } else {
        $error = "Failed to delete purchase order";
    }
    $stmt->close();
}

// Fetch all products from database
$sql = "SELECT p.*, u.username FROM product p LEFT JOIN user u ON p.user_id = u.user_id ORDER BY p.product_id DESC";
$result = $conn->query($sql);

// Fetch all categories for dropdown
$category_sql = "SELECT * FROM category ORDER BY category_name";
$category_result = $conn->query($category_sql);
$categories = [];
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch suppliers for dropdown
$supplier_sql = "SELECT * FROM supplier ORDER BY name";
$supplier_result = $conn->query($supplier_sql);
$suppliers = [];
if ($supplier_result) {
    while ($row = $supplier_result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Fetch products for dropdown
$product_list_sql = "SELECT product_id, product_name FROM product ORDER BY product_name";
$product_list_result = $conn->query($product_list_sql);
$products_list = [];
if ($product_list_result) {
    while ($row = $product_list_result->fetch_assoc()) {
        $products_list[] = $row;
    }
}

// Fetch purchase orders
$purchase_sql = "SELECT * FROM purchase_order ORDER BY order_date DESC";
$purchase_result = $conn->query($purchase_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster - Inventory</title>
    <link rel="stylesheet" href="assets/css/inventory.css">
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
            <div class="menu-item active">
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
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>

        <div class="content">
            <!-- Product Section -->
            <div class="section">
                <div class="section-header">
                    <div class="search-box">
                        <input type="text" placeholder="Search product" id="customerSearch" onkeyup="searchTable('customerTable')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </div>
                    <button class="add-btn" id="addUserBtn" onclick="openAddProductModal()">
                        <span>+</span>
                        <span>Add product</span>
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

                <div class="table-container">
                    <table id="customerTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category ID</th>
                                <th>User ID</th>
                                <th>Price</th>
                                <th>Unit</th>
                                <th>Current Stock</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                                        <td><?php echo number_format($row['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($row['current_stock']); ?></td>
                                        <td>
                                            <div class="actions">
                                                <button class="action-btn delete-btn" 
                                                        onclick="confirmDeleteProduct('<?php echo htmlspecialchars($row['product_id']); ?>', '<?php echo htmlspecialchars($row['product_name']); ?>')">
                                                    <svg fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                                    </svg>
                                                </button>
                                                <button class="action-btn edit-btn" 
                                                        onclick='openEditProductModal(<?php echo json_encode($row); ?>)'>
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
                                    <td colspan="8" style="text-align: center; padding: 30px;">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Purchase Order Section -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title">Purchase Order</div>
                    <div class="search-box">
                        <input type="text" placeholder="Search Purchase" id="salesSearch" onkeyup="searchTable('salesTable')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </div>
                    <button class="add-btn" onclick="openAddPurchaseModal()">
                        <span>+</span>
                        <span>Add Purchase Order</span>
                    </button>
                </div>

                <div class="table-container">
                    <table id="salesTable">
                        <thead>
                            <tr>
                                <th>Purchase ID</th>
                                <th>Sup ID</th>
                                <th>Product ID</th>
                                <th>Order Date</th>
                                <th>Exp Date</th>
                                <th>Total Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if ($purchase_result && $purchase_result->num_rows > 0): ?>
        <?php while($row = $purchase_result->fetch_assoc()): ?>
            <tr>
                <td>PU<?php echo str_pad($row['purchase_id'], 4, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($row['sup_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                <td><?php echo htmlspecialchars($row['exp_date']); ?></td>
                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                <td>
                    <div class="actions">
                        <button class="action-btn delete-btn" 
                                onclick="confirmDeletePurchase('<?php echo htmlspecialchars($row['purchase_id']); ?>', 'PU<?php echo str_pad($row['purchase_id'], 4, '0', STR_PAD_LEFT); ?>')">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg>
                        </button>
                        <button class="action-btn edit-btn" 
                                onclick='openEditPurchaseModal(<?php echo json_encode($row); ?>)'>
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
            <td colspan="7" style="text-align: center; padding: 30px;">No purchase orders found</td>
        </tr>
    <?php endif; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <span class="close" onclick="closeAddProductModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_product_id">Product ID *</label>
                        <input type="text" id="add_product_id" name="product_id" required placeholder="e.g., PR0001">
                    </div>

                    <div class="form-group">
                        <label for="add_product_name">Product Name *</label>
                        <input type="text" id="add_product_name" name="product_name" required placeholder="Enter product name">
                    </div>

                    <div class="form-group">
                        <label for="add_category_id">Category ID *</label>
                        <?php if (count($categories) > 0): ?>
                            <select id="add_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category_id']); ?>">
                                        <?php echo htmlspecialchars($cat['category_id'] . ' - ' . $cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="add_category_id" name="category_id" required placeholder="Enter category ID">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="add_price">Price (LKR) *</label>
                        <input type="number" id="add_price" name="price" step="0.01" min="0" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label for="add_unit">Unit *</label>
                        <input type="text" id="add_unit" name="unit" required placeholder="e.g., Piece, Box, Kg">
                    </div>

                    <div class="form-group">
                        <label for="add_current_stock">Current Stock *</label>
                        <input type="number" id="add_current_stock" name="current_stock" min="0" required placeholder="Enter quantity">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddProductModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <span class="close" onclick="closeEditProductModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_product_id" name="product_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_product_name">Product Name *</label>
                        <input type="text" id="edit_product_name" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_category_id">Category ID *</label>
                        <?php if (count($categories) > 0): ?>
                            <select id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category_id']); ?>">
                                        <?php echo htmlspecialchars($cat['category_id'] . ' - ' . $cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="edit_category_id" name="category_id" required>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="edit_price">Price (LKR) *</label>
                        <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_unit">Unit *</label>
                        <input type="text" id="edit_unit" name="unit" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_current_stock">Current Stock *</label>
                        <input type="number" id="edit_current_stock" name="current_stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditProductModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Purchase Order Modal -->
    <div id="addPurchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Purchase Order</h2>
                <span class="close" onclick="closeAddPurchaseModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_purchase">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_sup_id">Supplier ID *</label>
                        <?php if (count($suppliers) > 0): ?>
                            <select id="add_sup_id" name="sup_id" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?php echo htmlspecialchars($sup['sup_id']); ?>">
                                        <?php echo htmlspecialchars($sup['sup_id'] . ' - ' . $sup['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="add_sup_id" name="sup_id" required placeholder="Enter supplier ID">
                            <small>No suppliers found. Please add suppliers first.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="add_po_product_id">Product ID *</label>
                        <?php if (count($products_list) > 0): ?>
                            <select id="add_po_product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products_list as $prod): ?>
                                    <option value="<?php echo htmlspecialchars($prod['product_id']); ?>">
                                        <?php echo htmlspecialchars($prod['product_id'] . ' - ' . $prod['product_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="add_po_product_id" name="product_id" required placeholder="Enter product ID">
                            <small>No products found. Please add products first.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="add_order_date">Order Date *</label>
                        <input type="date" id="add_order_date" name="order_date" required>
                    </div>

                    <div class="form-group">
                        <label for="add_exp_date">Expire Date *</label>
                        <input type="date" id="add_exp_date" name="exp_date">
                    </div>

                    <div class="form-group">
                        <label for="add_total_amount">Total Amount (LKR) *</label>
                        <input type="number" id="add_total_amount" name="total_amount" step="0.01" min="0" required placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddPurchaseModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Purchase Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Purchase Order Modal -->
    <div id="editPurchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Purchase Order</h2>
                <span class="close" onclick="closeEditPurchaseModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_purchase">
                <input type="hidden" id="edit_purchase_id" name="purchase_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_sup_id">Supplier ID *</label>
                        <?php if (count($suppliers) > 0): ?>
                            <select id="edit_sup_id" name="sup_id" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?php echo htmlspecialchars($sup['sup_id']); ?>">
                                        <?php echo htmlspecialchars($sup['sup_id'] . ' - ' . $sup['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="number" id="edit_sup_id" name="sup_id" required>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="edit_po_product_id">Product ID *</label>
                        <?php if (count($products_list) > 0): ?>
                            <select id="edit_po_product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products_list as $prod): ?>
                                    <option value="<?php echo htmlspecialchars($prod['product_id']); ?>">
                                        <?php echo htmlspecialchars($prod['product_id'] . ' - ' . $prod['product_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="edit_po_product_id" name="product_id" required>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="edit_order_date">Order Date *</label>
                        <input type="date" id="edit_order_date" name="order_date" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_exp_date">Expected Delivery Date *</label>
                        <input type="date" id="edit_exp_date" name="exp_date" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_total_amount">Total Amount (LKR) *</label>
                        <input type="number" id="edit_total_amount" name="total_amount" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditPurchaseModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Purchase Order</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin-inventory.js"></script>
    <script>
        // Auto-show modal if there's an error from form submission
        <?php if ($error && isset($_POST['action'])): ?>
            <?php if ($_POST['action'] === 'add'): ?>
                openAddProductModal();
            <?php elseif ($_POST['action'] === 'edit'): ?>
                openEditProductModal({
                    product_id: '<?php echo addslashes($_POST['product_id']); ?>',
                    product_name: '<?php echo addslashes($_POST['product_name']); ?>',
                    category_id: '<?php echo addslashes($_POST['category_id']); ?>',
                    price: '<?php echo addslashes($_POST['price']); ?>',
                    unit: '<?php echo addslashes($_POST['unit']); ?>',
                    current_stock: '<?php echo addslashes($_POST['current_stock']); ?>'
                });
            <?php elseif ($_POST['action'] === 'add_purchase'): ?>
                openAddPurchaseModal();
            <?php elseif ($_POST['action'] === 'edit_purchase'): ?>
                openEditPurchaseModal({
                    purchase_id: '<?php echo addslashes($_POST['purchase_id']); ?>',
                    sup_id: '<?php echo addslashes($_POST['sup_id']); ?>',
                    product_id: '<?php echo addslashes($_POST['product_id']); ?>',
                    order_date: '<?php echo addslashes($_POST['order_date']); ?>',
                    exp_date: '<?php echo addslashes($_POST['exp_date']); ?>',
                    total_amount: '<?php echo addslashes($_POST['total_amount']); ?>'
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>

</html>