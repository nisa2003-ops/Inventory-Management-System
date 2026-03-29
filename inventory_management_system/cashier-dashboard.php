<?php
session_start();
include("config/db.php");

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if user is Cashier
if ($_SESSION['user_role'] !== 'Cashier') {
    echo "Access denied. You don't have permission to access this page.";
    exit();
}

// Fetch all products from database
$product_sql = "SELECT product_id, product_name, price FROM product ORDER BY product_name";
$product_result = $conn->query($product_sql);
$products = [];
if ($product_result) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch all customers from database
$customer_sql = "SELECT customer_id, customer_name FROM customers ORDER BY customer_name";
$customer_result = $conn->query($customer_sql);
$customers = [];
if ($customer_result) {
    while ($row = $customer_result->fetch_assoc()) {
        $customers[] = $row;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockMaster Pro - Cashier</title>
    <link rel="stylesheet" href="assets/css/cashier-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <h1><span>StockMaster</span> Pro</h1>
            </div>
            
            <div class="user-info">
                <span style="margin-right: 15px; color: #666;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <button class="logout-btn" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>

        <div class="main-content">
            <div class="panel">
                <h2 class="panel-title"><i class="fas fa-box-open"></i> Products & Customers</h2>
                
                <div class="section-title">
                    Products <span id="productCount"><?php echo count($products); ?> items</span>
                </div>
                <div class="input-group">
                    <input type="text" id="productSearch" class="input-field" placeholder="Search product by name...">
                    <input type="number" id="unitInput" class="input-field small" placeholder="Qty" min="1" value="1">
                    <button class="add-btn" onclick="addToCart()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div class="list-container" id="productListContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item" onclick="selectProduct('<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo $product['price']; ?>, '<?php echo htmlspecialchars($product['product_id']); ?>')">
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                <div class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></div>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($products)): ?>
                        <div style="text-align: center; padding: 30px; color: #999;">
                            No products available
                        </div>
                    <?php endif; ?>
                </div>

                <div class="section-title" style="margin-top: 30px;">
                    Customers <span id="customerCount"><?php echo count($customers); ?> customers</span>
                </div>
                <div class="input-group">
                    <input type="text" id="customerSearch" class="input-field" placeholder="Search customer by name...">
                </div>

                <div class="list-container" id="customerListContainer">
                    <?php foreach ($customers as $customer): ?>
                        <div class="customer-item" onclick="selectCustomer('<?php echo htmlspecialchars($customer['customer_name']); ?>', <?php echo $customer['customer_id']; ?>)">
                            <div class="customer-info">
                                <div class="customer-name"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
                                <div class="customer-id">CU<?php echo str_pad($customer['customer_id'], 4, '0', STR_PAD_LEFT); ?></div>
                            </div>
                            <i class="fas fa-user-check"></i>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($customers)): ?>
                        <div style="text-align: center; padding: 30px; color: #999;">
                            No customers found
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel cart-container">
                <h2 class="panel-title"><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
                
                <div class="selected-customer" id="selectedCustomer">
                    <h3 id="customerName"></h3>
                    <p id="customerId"></p>
                </div>

                <div class="cart-items" id="cartItems">
                    <div class="empty-cart" id="emptyCart">
                        <i class="fas fa-shopping-basket"></i>
                        <h3>Your cart is empty</h3>
                        <p>Add products from the list to get started</p>
                    </div>
                </div>

                <div class="total-section">
                    <div class="total-row">
                        <span>Total Amount:</span>
                        <span id="totalAmount">Rs. 0.00</span>
                    </div>
                    <button class="generate-btn" onclick="generateBill()">
                        <i class="fas fa-receipt"></i>
                        Generate Bill
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong id="notificationTitle">Success!</strong>
            <p id="notificationMessage">Item added to cart successfully.</p>
        </div>
    </div>
    
    <script src="assets/js/cashier-dashboard.js"></script>
</body>
</html>