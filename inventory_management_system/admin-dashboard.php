<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user_role'] !== 'Admin') {
    echo "Access denied";
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "inventory_store");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. TOTAL EMPLOYEES (Count of all users in the user table)
$employees_query = "SELECT COUNT(*) as total_employees FROM user";
$employees_result = $conn->query($employees_query);
$employees_data = $employees_result->fetch_assoc();
$total_employees = $employees_data['total_employees'];

// 2. TOTAL PRODUCTS (Count of all products in the product table)
$products_query = "SELECT COUNT(*) as total_products FROM product";
$products_result = $conn->query($products_query);
$products_data = $products_result->fetch_assoc();
$total_products = $products_data['total_products'];

// 3. INVENTORY VALUE (Sum of price * current_stock for all products)
$inventory_query = "
    SELECT COALESCE(SUM(price * current_stock), 0) AS inventory_value
    FROM product;";
$inventory_result = $conn->query($inventory_query);
$inventory_data = $inventory_result->fetch_assoc();
$inventory_value = $inventory_data['inventory_value'];


// 4. TOTAL CATEGORIES (Count of all categories in the category table)
$categories_query = "SELECT COUNT(*) as total_categories FROM category";
$categories_result = $conn->query($categories_query);
$categories_data = $categories_result->fetch_assoc();
$total_categories = $categories_data['total_categories'];

// 5. TOTAL SUPPLIERS (Count of all suppliers in the supplier table)
$suppliers_query = "SELECT COUNT(*) as total_suppliers FROM supplier";
$suppliers_result = $conn->query($suppliers_query);
$suppliers_data = $suppliers_result->fetch_assoc();
$total_suppliers = $suppliers_data['total_suppliers'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <title>StockMaster Dashboard</title>
</head>

<body>
    <div class="sidebar">
        <div class="logo">StockMaster</div>
        <div class="menu">
            <div class="menu-item active">
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
            Welcome
        </div>
        <div class="dashboard">
            <div class="cards-grid">
                <div class="card primary">
                    <div class="card-value"><?php echo $total_products; ?></div>
                    <div class="card-label">Total Product</div>
                </div>
                <div class="card secondary">
                    <div class="card-value">Rs<?php echo number_format($inventory_value, 0); ?></div>
                    <div class="card-label">Inventory Value</div>
                </div>
                <div class="card tertiary">
                    <div class="card-value"><?php echo $total_employees; ?></div>
                    <div class="card-label">Total Employees</div>
                </div>
                <div class="card light">
                    <div class="card-value"><?php echo $total_categories; ?></div>
                    <div class="card-label">Total Categories</div>
                </div>
                <div class="card light-2">
                    <div class="card-value"><?php echo $total_suppliers; ?></div>
                    <div class="card-label">Total Suppliers</div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/admin-dashboard.js"></script>
</body>

</html>