// ===== Add Product Modal Functions =====
function openAddProductModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

function closeAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
    document.querySelector('#addProductModal form').reset();
}

// ===== Edit Product Modal Functions =====
function openEditProductModal(productData) {
    document.getElementById('edit_product_id').value = productData.product_id;
    document.getElementById('edit_product_name').value = productData.product_name;
    document.getElementById('edit_category_id').value = productData.category_id;
    document.getElementById('edit_price').value = productData.price;
    document.getElementById('edit_unit').value = productData.unit;
    document.getElementById('edit_current_stock').value = productData.current_stock;
    document.getElementById('editProductModal').style.display = 'block';
}

function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
    document.querySelector('#editProductModal form').reset();
}

// ===== Add Purchase Order Modal Functions =====
function openAddPurchaseModal() {
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('add_order_date').value = today;
    document.getElementById('addPurchaseModal').style.display = 'block';
}

function closeAddPurchaseModal() {
    document.getElementById('addPurchaseModal').style.display = 'none';
    document.querySelector('#addPurchaseModal form').reset();
}

// ===== Edit Purchase Order Modal Functions =====
function openEditPurchaseModal(purchaseData) {
    document.getElementById('edit_purchase_id').value = purchaseData.purchase_id;
    document.getElementById('edit_sup_id').value = purchaseData.sup_id;
    document.getElementById('edit_po_product_id').value = purchaseData.product_id;
    document.getElementById('edit_order_date').value = purchaseData.order_date;
    document.getElementById('edit_exp_date').value = purchaseData.exp_date;
    document.getElementById('edit_total_amount').value = purchaseData.total_amount;
    document.getElementById('editPurchaseModal').style.display = 'block';
}

function closeEditPurchaseModal() {
    document.getElementById('editPurchaseModal').style.display = 'none';
    document.querySelector('#editPurchaseModal form').reset();
}

// ===== Close Modals When Clicking Outside =====
window.onclick = function(event) {
    const addModal = document.getElementById('addProductModal');
    const editModal = document.getElementById('editProductModal');
    const addPurchaseModal = document.getElementById('addPurchaseModal');
    const editPurchaseModal = document.getElementById('editPurchaseModal');
    
    if (event.target == addModal) {
        closeAddProductModal();
    }
    if (event.target == editModal) {
        closeEditProductModal();
    }
    if (event.target == addPurchaseModal) {
        closeAddPurchaseModal();
    }
    if (event.target == editPurchaseModal) {
        closeEditPurchaseModal();
    }
}

// ===== Search Table Function =====
function searchTable(tableId) {
    const searchInput = tableId === 'customerTable' ? 
        document.getElementById('customerSearch') : 
        document.getElementById('salesSearch');
    
    const filter = searchInput.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;

        // Search through all columns except the last one (actions)
        for (let j = 0; j < cells.length - 1; j++) {
            if (cells[j]) {
                const txtValue = cells[j].textContent || cells[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }

        rows[i].style.display = found ? '' : 'none';
    }
}

function confirmDeletePurchase(purchaseId, purchaseCode) {
    if (confirm(`Are you sure you want to delete purchase order "${purchaseCode}"?\n\nThis action cannot be undone.`)) {
        window.location.href = 'inventory.php?delete_purchase_id=' + encodeURIComponent(purchaseId);
    }
}

// ===== Delete Product Confirmation =====
function confirmDeleteProduct(productId, productName) {
    if (confirm(`Are you sure you want to delete product "${productName}"?\n\nThis action cannot be undone.`)) {
        window.location.href = 'inventory.php?delete_id=' + encodeURIComponent(productId);
    }
}

// ===== Sidebar Navigation =====
document.addEventListener('DOMContentLoaded', function() {
    // Menu item navigation
    document.querySelectorAll(".menu-item").forEach((item) => {
        const span = item.querySelector('span');
        if (span) {
            const text = span.textContent.trim();
            
            item.addEventListener("click", () => {
                switch(text) {
                    case 'Dashboard':
                        window.location.href = "admin-dashboard.php";
                        break;
                    case 'Inventory':
                        window.location.href = "admin-inventory.php";
                        break
                    case 'Suppliers':
                        window.location.href = "admin-suppliers.php";
                        break;
                    case 'Customers':
                        window.location.href = "admin-customers.php";
                        break;
                    case 'Settings':
                        window.location.href = "admin-settings.php";
                        break;
                }
            });
        }
    });

    // Logout confirmation
    const logoutBtn = document.querySelector(".logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "index.php";
            }
        });
    }
});