// Enhanced StockMaster Application
let cart = [];
let selectedProductName = '';
let selectedProductPrice = 0;
let selectedProductId = '';
let selectedCustomerData = null;

// DOM Elements
const productSearch = document.getElementById('productSearch');
const customerSearch = document.getElementById('customerSearch');
const unitInput = document.getElementById('unitInput');
const cartItems = document.getElementById('cartItems');
const totalAmount = document.getElementById('totalAmount');
const emptyCart = document.getElementById('emptyCart');
const notification = document.getElementById('notification');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateEmptyCartVisibility();
});

function selectProduct(name, price, productId) {
    // Remove previous selection
    document.querySelectorAll('.product-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('selected');
    
    selectedProductName = name;
    selectedProductPrice = price;
    selectedProductId = productId;
    productSearch.value = name;
    
    // Show notification
    showNotification('Product Selected', `${name} selected. Ready to add to cart.`);
}

function selectCustomer(name, id) {
    // Remove previous selection
    document.querySelectorAll('.customer-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('selected');
    
    selectedCustomerData = { name, id };
    document.getElementById('customerName').textContent = name;
    document.getElementById('customerId').textContent = `Customer ID: CU${String(id).padStart(4, '0')}`;
    document.getElementById('selectedCustomer').classList.add('active');
    
    // Show notification
    showNotification('Customer Selected', `${name} is now the active customer.`);
}

function addToCart() {
    if (!selectedProductName) {
        showNotification('No Product Selected', 'Please select a product first.', 'error');
        return;
    }

    const units = parseInt(unitInput.value) || 1;
    
    if (units <= 0) {
        showNotification('Invalid Quantity', 'Please enter a valid quantity.', 'error');
        return;
    }
    
    const existingIndex = cart.findIndex(item => item.name === selectedProductName);
    
    if (existingIndex >= 0) {
        cart[existingIndex].quantity += units;
        showNotification('Quantity Updated', `${selectedProductName} quantity increased by ${units}.`);
    } else {
        cart.push({
            name: selectedProductName,
            price: selectedProductPrice,
            quantity: units,
            product_id: selectedProductId
        });
        showNotification('Product Added', `${selectedProductName} added to cart.`);
    }

    updateCart();
    productSearch.value = '';
    unitInput.value = 1;
    selectedProductName = '';
    selectedProductPrice = 0;
    selectedProductId = '';
    
    // Remove selection
    document.querySelectorAll('.product-item').forEach(item => {
        item.classList.remove('selected');
    });
}

function removeFromCart(index) {
    const removedItem = cart[index];
    cart.splice(index, 1);
    updateCart();
    showNotification('Item Removed', `${removedItem.name} removed from cart.`);
}

function updateCart() {
    cartItems.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        emptyCart.style.display = 'block';
    } else {
        emptyCart.style.display = 'none';
        
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <button class="remove-btn" onclick="removeFromCart(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <span class="product-name">${item.name}</span>
                <div class="quantity-control">
                    <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                </div>
                <span class="item-total">Rs. ${itemTotal.toFixed(2)}</span>
            `;
            cartItems.appendChild(cartItem);
        });
    }

    totalAmount.textContent = `Rs. ${total.toFixed(2)}`;
    updateEmptyCartVisibility();
}

function updateQuantity(index, change) {
    if (cart[index].quantity + change < 1) {
        removeFromCart(index);
        return;
    }
    
    cart[index].quantity += change;
    updateCart();
    showNotification('Quantity Updated', `${cart[index].name} quantity updated.`);
}

function updateEmptyCartVisibility() {
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
    } else {
        emptyCart.style.display = 'none';
    }
}

async function generateBill() {
    if (cart.length === 0) {
        showNotification('Empty Cart', 'Your cart is empty! Please add items first.', 'error');
        return;
    }

    // Calculate total
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
    });

    // Prepare data to send to server
    // customer_id is optional now (can be null)
    const billData = {
        customer_id: selectedCustomerData ? selectedCustomerData.id : null,
        cart_items: cart.map(item => ({
            product_id: item.product_id,
            price: item.price,
            quantity: item.quantity
        })),
        total_amount: total
    };

    try {
        // Save bill to database
        const response = await fetch('save_bill.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(billData)
        });

        const result = await response.json();

        if (result.success) {
            // Show bill display
            const customerName = selectedCustomerData ? selectedCustomerData.name : 'Walk-in Customer';
            const customerId = selectedCustomerData ? selectedCustomerData.id : null;
            displayBill(result.order_id, result.formatted_order_id, total, customerName, customerId);
            
            // Reset cart and customer
            cart = [];
            selectedCustomerData = null;
            document.getElementById('selectedCustomer').classList.remove('active');
            document.querySelectorAll('.customer-item').forEach(item => {
                item.classList.remove('selected');
            });
            updateCart();
            
            showNotification('Bill Saved', `Order ${result.formatted_order_id} saved successfully!`);
        } else {
            showNotification('Error', result.message, 'error');
        }
    } catch (error) {
        console.error('Error saving bill:', error);
        showNotification('Error', 'Failed to save bill. Please try again.', 'error');
    }
}

function displayBill(orderId, formattedOrderId, total, customerName = 'Walk-in Customer', customerId = null) {
    const date = new Date().toLocaleDateString();
    const time = new Date().toLocaleTimeString();
    
    const customerIdDisplay = customerId ? `CU${String(customerId).padStart(4, '0')}` : 'N/A';
    
    const billDisplay = `
        <div style="font-family: monospace; background: #f8f9fa; padding: 20px; border-radius: 10px; max-width: 400px; margin: 0 auto; border-left: 5px solid #ff5722;">
            <h3 style="text-align: center; color: #ff5722; margin-bottom: 20px;">STOCKMASTER PRO BILL</h3>
            <p><strong>Order ID:</strong> ${formattedOrderId}</p>
            <p><strong>Date:</strong> ${date} | <strong>Time:</strong> ${time}</p>
            <p><strong>Customer:</strong> ${customerName}</p>
            <p><strong>Customer ID:</strong> ${customerIdDisplay}</p>
            <hr style="margin: 15px 0;">
            <div style="margin-bottom: 15px;">
                ${cart.map(item => {
                    const itemTotal = item.price * item.quantity;
                    return `<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>${item.name} (${item.quantity}x)</span>
                        <span>Rs. ${itemTotal.toFixed(2)}</span>
                    </div>`;
                }).join('')}
            </div>
            <hr style="margin: 15px 0;">
            <div style="display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold;">
                <span>TOTAL:</span>
                <span>Rs. ${total.toFixed(2)}</span>
            </div>
            <p style="text-align: center; margin-top: 20px; color: #6c757d;">Thank you for your purchase!</p>
        </div>
    `;

    // Create modal for bill display
    const billModal = document.createElement('div');
    billModal.className = 'modal';
    billModal.style.display = 'flex';
    billModal.innerHTML = `
        <div class="modal-content">
            <h2><i class="fas fa-receipt"></i> Bill Generated Successfully</h2>
            ${billDisplay}
            <div class="modal-buttons" style="margin-top: 30px;">
                <button class="modal-btn cancel" onclick="this.parentElement.parentElement.parentElement.remove()">Close</button>
                <button class="modal-btn confirm" onclick="printBill()">
                    <i class="fas fa-print"></i> Print Bill
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(billModal);
}

function printBill() {
    window.print();
}

// Search functionality
productSearch.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    let visibleCount = 0;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.querySelector('.product-name').textContent.toLowerCase();
        if (name.includes(search)) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('productCount').textContent = `${visibleCount} items`;
});

customerSearch.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    let visibleCount = 0;
    
    document.querySelectorAll('.customer-item').forEach(item => {
        const name = item.querySelector('.customer-name').textContent.toLowerCase();
        if (name.includes(search)) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('customerCount').textContent = `${visibleCount} customers`;
});

// Logout Confirmation
document.getElementById("logoutBtn").addEventListener("click", function () {
    if (!confirm("Are you sure you want to logout?")) return;
    window.location.href = "index.php";
});

// Notification system
function showNotification(title, message, type = 'success') {
    const notificationTitle = document.getElementById('notificationTitle');
    const notificationMessage = document.getElementById('notificationMessage');
    
    notificationTitle.textContent = title;
    notificationMessage.textContent = message;
    
    notification.className = 'notification';
    notification.classList.add('show');
    
    if (type === 'error') {
        notification.classList.add('error');
        notification.querySelector('i').className = 'fas fa-exclamation-circle';
    } else {
        notification.querySelector('i').className = 'fas fa-check-circle';
    }
    
    // Auto hide after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 4000);
}

// Add keyboard shortcuts
document.addEventListener('keydown', function(event) {
    // Ctrl + Enter to add to cart
    if (event.ctrlKey && event.key === 'Enter') {
        event.preventDefault();
        addToCart();
    }
    
    // F2 to generate bill
    if (event.key === 'F2') {
        event.preventDefault();
        generateBill();
    }
});