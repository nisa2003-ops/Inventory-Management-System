<?php
session_start();
include("config/db.php");

// Set JSON header
header('Content-Type: application/json');

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if user is Cashier
if ($_SESSION['user_role'] !== 'Cashier') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data: ' . json_last_error_msg()]);
    exit();
}

$customer_id = isset($data['customer_id']) ? intval($data['customer_id']) : null;
$user_id = $_SESSION['user_id'];
$cart_items = $data['cart_items'] ?? [];
$total_amount = floatval($data['total_amount'] ?? 0);

// Validation
if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

if ($total_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid total amount']);
    exit();
}

// Verify customer exists if provided
if ($customer_id && $customer_id !== 0) {
    $check_customer = "SELECT customer_id FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($check_customer);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit();
    }
    $stmt->close();
} else {
    $customer_id = null;
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert into sales_order
    $sales_date = date('Y-m-d H:i:s');
    $insert_order = "INSERT INTO sales_order (customer_id, user_id, sales_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_order);
    $stmt->bind_param("iis", $customer_id, $user_id, $sales_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create sales order: " . $stmt->error);
    }
    
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Insert each cart item into sales_item and update stock
    $insert_item = "INSERT INTO sales_item (order_id, unit_price, quantity) VALUES (?, ?, ?)";
    $stmt_item = $conn->prepare($insert_item);
    
    $update_stock = "UPDATE product SET current_stock = current_stock - ? WHERE product_id = ?";
    $stmt_stock = $conn->prepare($update_stock);
    
    foreach ($cart_items as $item) {
        $unit_price = floatval($item['price']);
        $quantity = intval($item['quantity']);
        $product_id = $item['product_id'];
        
        // Insert into sales_item
        $stmt_item->bind_param("idi", $order_id, $unit_price, $quantity);
        
        if (!$stmt_item->execute()) {
            throw new Exception("Failed to add item to sales: " . $stmt_item->error);
        }
        
        // Update product stock
        $stmt_stock->bind_param("is", $quantity, $product_id);
        
        if (!$stmt_stock->execute()) {
            throw new Exception("Failed to update product stock: " . $stmt_stock->error);
        }
    }
    
    $stmt_item->close();
    $stmt_stock->close();
    
    // Commit transaction
    $conn->commit();
    
    // Return success with order ID
    echo json_encode([
        'success' => true, 
        'message' => 'Bill saved successfully!',
        'order_id' => $order_id,
        'formatted_order_id' => 'OR' . str_pad($order_id, 4, '0', STR_PAD_LEFT)
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>