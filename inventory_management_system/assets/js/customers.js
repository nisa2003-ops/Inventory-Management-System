// customers.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initSearchFunctionality();
    initTableSorting();
    initMenuNavigation();
    initLogout();
    addHoverEffects();
});

// ===== Modal Functions for PHP onclick handlers =====
function openAddCustomerModal() {
    const modal = document.getElementById('addCustomerModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.style.display = 'flex';
    modalContent.style.transform = 'translateY(20px)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modalContent.style.transition = 'all 0.3s ease';
        modalContent.style.transform = 'translateY(0)';
        modalContent.style.opacity = '1';
    }, 10);
}

function closeAddCustomerModal() {
    const modal = document.getElementById('addCustomerModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modalContent.style.transform = 'translateY(20px)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function openEditCustomerModal(customerData) {
    document.getElementById('edit_customer_id').value = customerData.customer_id;
    document.getElementById('edit_customer_name').value = customerData.customer_name;
    document.getElementById('edit_email').value = customerData.customer_email;
    document.getElementById('edit_phone').value = customerData.customer_phone;
    
    const modal = document.getElementById('editCustomerModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.style.display = 'flex';
    modalContent.style.transform = 'translateY(20px)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modalContent.style.transition = 'all 0.3s ease';
        modalContent.style.transform = 'translateY(0)';
        modalContent.style.opacity = '1';
    }, 10);
}

function closeEditCustomerModal() {
    const modal = document.getElementById('editCustomerModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modalContent.style.transform = 'translateY(20px)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function confirmDeleteCustomer(customerId, customerName) {
    if (confirm(`Are you sure you want to delete ${customerName}?\n\nThis action cannot be undone.`)) {
        window.location.href = `?delete_id=${customerId}`;
    }
}

function searchTable(tableId) {
    const searchInput = tableId === 'customerTable' ? 
        document.getElementById('customerSearch') : 
        document.getElementById('salesSearch');
    
    if (searchInput) {
        filterTable(tableId, searchInput.value);
    }
}

// ===== Search functionality =====
function initSearchFunctionality() {
    const customerSearch = document.getElementById('customerSearch');
    const salesSearch = document.getElementById('salesSearch');

    if (customerSearch) {
        customerSearch.addEventListener('input', function(e) {
            filterTable('customerTable', e.target.value);
        });
    }

    if (salesSearch) {
        salesSearch.addEventListener('input', function(e) {
            filterTable('salesTable', e.target.value);
        });
    }
}

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    searchTerm = searchTerm.toLowerCase().trim();

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let rowText = '';

        cells.forEach((cell, index) => {
            if (index < cells.length - 1) {
                rowText += cell.textContent.toLowerCase() + ' ';
            }
        });

        row.style.display = rowText.includes(searchTerm) ? '' : 'none';
    });
}

// ===== Table Sorting =====
function initTableSorting() {
    document.querySelectorAll('th').forEach((th, index) => {
        if (index < 4) {
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                const table = this.closest('table');
                const columnIndex = index;
                sortTable(table.id, columnIndex);
            });
        }
    });
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();

        if (aText < bText) return -1;
        if (aText > bText) return 1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

// ===== Menu Navigation =====
function initMenuNavigation() {
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.menu-item').forEach(i => {
                i.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

document.querySelectorAll(".menu-item").forEach((item) => {
    if (item.innerText.includes("Dashboard")) {
        item.addEventListener("click", () => {
            window.location.href = "manager-dashboard.php";
        });
    }
});

document.querySelectorAll(".menu-item").forEach((item) => {
    if (item.innerText.includes("Users")) {
        item.addEventListener("click", () => {
            window.location.href = "users.php";
        });
    }
});

document.querySelectorAll(".menu-item").forEach((item) => {
    if (item.innerText.includes("Inventory")) {
        item.addEventListener("click", () => {
            window.location.href = "inventory.php";
        });
    }
});

document.querySelectorAll(".menu-item").forEach((item) => {
    if (item.innerText.includes("Suppliers")) {
        item.addEventListener("click", () => {
            window.location.href = "suppliers.php";
        });
    }
});

document.querySelectorAll(".menu-item").forEach((item) => {
    if (item.innerText.includes("Settings")) {
        item.addEventListener("click", () => {
            window.location.href = "settings.php";
        });
    }
});

// ===== Logout Confirmation =====
function initLogout() {
    const logoutBtn = document.querySelector(".logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            const confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "index.php";
            }
        });
    }
}

// ===== Hover Effects =====
function addHoverEffects() {
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f9fafb';
        });

        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    const addBtn = document.getElementById('addUserBtn');
    if (addBtn) {
        addBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        addBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    }
}

// ===== Close modal when clicking outside =====
window.addEventListener('click', function(event) {
    const addModal = document.getElementById('addCustomerModal');
    const editModal = document.getElementById('editCustomerModal');
    
    if (event.target === addModal) {
        closeAddCustomerModal();
    }
    if (event.target === editModal) {
        closeEditCustomerModal();
    }
});