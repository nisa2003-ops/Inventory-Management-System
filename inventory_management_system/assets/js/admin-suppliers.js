// ===== Modal Management =====
function openAddSupplierModal() {
    const modal = document.createElement('div');
    modal.className = 'modal-backdrop';
    modal.innerHTML = `
        <div class="supplier-modal">
            <div class="modal-header">
                <h3>Add New Supplier</h3>
                <button class="close-modal" onclick="this.closest('.modal-backdrop').remove()">×</button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm">
                    <div class="form-group">
                        <label for="supplier_name">Supplier Name</label>
                        <input type="text" id="supplier_name" placeholder="Enter supplier name" required>
                    </div>
                    <div class="form-group">
                        <label for="supplier_phone">Phone Number</label>
                        <input type="tel" id="supplier_phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <label for="supplier_address">Address</label>
                        <textarea id="supplier_address" placeholder="Enter supplier address" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="this.closest('.modal-backdrop').remove()">Cancel</button>
                        <button type="submit" class="submit-btn">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('addSupplierForm').addEventListener('submit', handleAddSupplier);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

function openEditSupplierModal(sup_id, name, phone, address) {
    const modal = document.createElement('div');
    modal.className = 'modal-backdrop';
    modal.innerHTML = `
        <div class="supplier-modal">
            <div class="modal-header">
                <h3>Edit Supplier</h3>
                <button class="close-modal" onclick="this.closest('.modal-backdrop').remove()">×</button>
            </div>
            <div class="modal-body">
                <form id="editSupplierForm">
                    <input type="hidden" id="edit_sup_id" value="${sup_id}">
                    <div class="form-group">
                        <label for="edit_supplier_name">Supplier Name</label>
                        <input type="text" id="edit_supplier_name" value="${name}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier_phone">Phone Number</label>
                        <input type="tel" id="edit_supplier_phone" value="${phone}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier_address">Address</label>
                        <textarea id="edit_supplier_address" required>${address}</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="this.closest('.modal-backdrop').remove()">Cancel</button>
                        <button type="submit" class="submit-btn">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('editSupplierForm').addEventListener('submit', handleEditSupplier);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

function openDeleteConfirmModal(sup_id, name) {
    const modal = document.createElement('div');
    modal.className = 'modal-backdrop';
    modal.innerHTML = `
        <div class="delete-modal">
            <div class="modal-header">
                <h3>Delete Supplier</h3>
                <button class="close-modal" onclick="this.closest('.modal-backdrop').remove()">×</button>
            </div>
            <div class="modal-body">
                <div class="warning-icon">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
                <h4 style="text-align: center; margin-bottom: 8px; color: #374151;">Delete "${name}"?</h4>
                <p class="warning-text" style="text-align: center;">This action cannot be undone. All associated data may be affected.</p>
                <div class="form-actions" style="margin-top: 24px;">
                    <button type="button" class="cancel-btn" onclick="this.closest('.modal-backdrop').remove()">Cancel</button>
                    <button type="button" class="delete-confirm-btn" onclick="confirmDelete(${sup_id})">Delete</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

// ===== Form Handlers =====
function handleAddSupplier(e) {
    e.preventDefault();
    
    const name = document.getElementById('supplier_name').value.trim();
    const phone = document.getElementById('supplier_phone').value.trim();
    const address = document.getElementById('supplier_address').value.trim();
    
    if (!validateInputs(name, phone, address)) return;
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('address', address);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier added successfully', 'success');
            document.querySelector('.modal-backdrop').remove();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function handleEditSupplier(e) {
    e.preventDefault();
    
    const sup_id = document.getElementById('edit_sup_id').value;
    const name = document.getElementById('edit_supplier_name').value.trim();
    const phone = document.getElementById('edit_supplier_phone').value.trim();
    const address = document.getElementById('edit_supplier_address').value.trim();
    
    if (!validateInputs(name, phone, address)) return;
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('sup_id', sup_id);
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('address', address);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier updated successfully', 'success');
            document.querySelector('.modal-backdrop').remove();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function confirmDelete(sup_id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('sup_id', sup_id);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier deleted successfully', 'success');
            document.querySelector('.modal-backdrop').remove();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// ===== Validation =====
function validateInputs(name, phone, address) {
    if (!name || !phone || !address) {
        showToast('All fields are required', 'error');
        return false;
    }
    
    const phoneDigits = phone.replace(/\D/g, '');
    if (!/^[0-9]{10}$/.test(phoneDigits)) {
        showToast('Phone number must contain 10 digits', 'error');
        return false;
    }
    
    if (name.length < 3) {
        showToast('Supplier name must be at least 3 characters', 'error');
        return false;
    }
    
    if (address.length < 5) {
        showToast('Address must be at least 5 characters', 'error');
        return false;
    }
    
    return true;
}

// ===== Search Functionality =====
function handleSearch() {
    const searchInput = document.getElementById('supplierSearch').value.trim();
    
    if (!searchInput) {
        location.reload();
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('search', searchInput);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTable(data.suppliers);
        } else {
            showToast('Search failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred during search', 'error');
    });
}

function updateTable(suppliers) {
    const tbody = document.querySelector('#supplierTable tbody');
    tbody.innerHTML = '';
    
    if (suppliers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #999;">No suppliers found</td></tr>';
        return;
    }
    
    suppliers.forEach(supplier => {
        const row = document.createElement('tr');
        row.className = 'highlight-search';
        row.innerHTML = `
            <td>${escapeHtml(supplier.name)}</td>
            <td>${escapeHtml(supplier.sup_id)}</td>
            <td>${escapeHtml(supplier.phone_no)}</td>
            <td>${escapeHtml(supplier.address)}</td>
            <td>
                <div class="actions">
                    <button class="action-btn delete-btn" onclick="openDeleteConfirmModal(${supplier.sup_id}, '${escapeHtml(supplier.name)}')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                    <button class="action-btn edit-btn" onclick="openEditSupplierModal(${supplier.sup_id}, '${escapeHtml(supplier.name)}', '${escapeHtml(supplier.phone_no)}', '${escapeHtml(supplier.address)}')">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// ===== Toast Notifications =====
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'success' ? 'toast-success' : 'toast-error'}`;
    toast.innerHTML = `
        <div class="toast-icon">
            ${type === 'success' ? 
                '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>' :
                '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
            }
        </div>
        <span class="toast-message">${message}</span>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== Utility Functions =====
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== Sidebar Navigation =====
function setupNavigation() {
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
                        break;
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

    const logoutBtn = document.querySelector(".logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "index.php";
            }
        });
    }
}

// ===== Initialize on Page Load =====
document.addEventListener('DOMContentLoaded', function() {
    setupNavigation();
    
    // Add supplier button
    const addBtn = document.querySelector('.add-btn');
    if (addBtn) {
        addBtn.addEventListener('click', openAddSupplierModal);
    }
    
    // Search functionality
    const searchInput = document.getElementById('supplierSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(handleSearch, 300);
        });
    }
    
    // Edit and delete buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            const name = row.cells[0].textContent;
            const sup_id = row.cells[1].textContent;
            const phone = row.cells[2].textContent;
            const address = row.cells[3].textContent;
            
            openEditSupplierModal(sup_id, name, phone, address);
        });
    });
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            const name = row.cells[0].textContent;
            const sup_id = row.cells[1].textContent;
            
            openDeleteConfirmModal(sup_id, name);
        });
    });
});