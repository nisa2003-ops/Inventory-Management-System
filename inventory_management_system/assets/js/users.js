// ===== Add User Modal Functions =====
function openAddModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addUserModal').style.display = 'none';
    document.querySelector('#addUserModal form').reset();
}

// ===== Edit User Modal Functions =====
function openEditModal(userData) {
    document.getElementById('edit_user_id').value = userData.user_id;
    document.getElementById('edit_username').value = userData.username;
    document.getElementById('edit_role').value = userData.role;
    document.getElementById('edit_password').value = '';
    document.getElementById('editUserModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
    document.querySelector('#editUserModal form').reset();
}

// ===== Close Modals When Clicking Outside =====
window.onclick = function(event) {
    const addModal = document.getElementById('addUserModal');
    const editModal = document.getElementById('editUserModal');
    
    if (event.target == addModal) {
        closeAddModal();
    }
    if (event.target == editModal) {
        closeEditModal();
    }
}

// ===== Form Validation for Add User =====
function validateAddForm() {
    const password = document.getElementById('add_password').value;
    const confirmPassword = document.getElementById('add_confirm_password').value;

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return false;
    }

    if (password.length < 6) {
        alert('Password must be at least 6 characters long!');
        return false;
    }

    return true;
}

// ===== Search Users Function =====
function searchUsers() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('userTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;

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

// ===== Delete Confirmation =====
function confirmDelete(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"?`)) {
        window.location.href = 'users.php?delete_id=' + userId;
    }
}

// ===== Sidebar Navigation =====
document.addEventListener('DOMContentLoaded', function() {
    // Dashboard navigation
    document.querySelectorAll(".menu-item").forEach((item) => {
        const span = item.querySelector('span');
        if (span) {
            const text = span.textContent.trim();
            
            item.addEventListener("click", () => {
                switch(text) {
                    case 'Dashboard':
                        window.location.href = "manager-dashboard.php";
                        break;
                    case 'Inventory':
                        window.location.href = "inventory.php";
                        break;
                    case 'Users':
                        window.location.href = "users.php";
                        break;
                    case 'Suppliers':
                        window.location.href = "suppliers.php";
                        break;
                    case 'Customers':
                        window.location.href = "customers.php";
                        break;
                    case 'Settings':
                        window.location.href = "settings.php";
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