document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.querySelector(".edit-btn");
    
    if (!editBtn) return;

    // Get current values
    const userIdSpan = document.getElementById("userId");
    const userNameSpan = document.getElementById("userName");
    const userRoleSpan = document.getElementById("userRole");

    // Create modal HTML
    const modal = document.createElement("div");
    modal.className = "modal";
    modal.id = "editUserModal";
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" id="userId" value="${userIdSpan.textContent}" disabled>
                        <small>User ID cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>User Name</label>
                        <input type="text" id="editName" value="${userNameSpan.textContent}" required>
                        <small>Username must be between 3 and 50 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select id="editRole" required>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                        </select>
                        <small>Select role between Admin and Manager</small>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password (Optional)</label>
                        <input type="password" id="editPassword" placeholder="Leave blank to keep current password">
                        <small>Password must be at least 6 characters if changed</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBtn">Save Changes</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    const closeBtn = modal.querySelector(".close");
    const cancelBtn = modal.querySelector("#cancelBtn");
    const saveBtn = modal.querySelector("#saveBtn");
    const editForm = modal.querySelector("#editForm");
    const editRole = modal.querySelector("#editRole");

    // Hide modal initially
    modal.style.display = "none";

    // Open modal
    editBtn.addEventListener("click", () => {
        document.querySelector("#editName").value = userNameSpan.textContent;
        editRole.value = userRoleSpan.textContent;
        document.querySelector("#editPassword").value = "";
        modal.style.display = "block";
    });

    // Close modal functions
    const closeModal = () => {
        modal.style.display = "none";
    };

    closeBtn.addEventListener("click", closeModal);
    cancelBtn.addEventListener("click", closeModal);

    // Close when clicking outside modal
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Save changes
    saveBtn.addEventListener("click", () => {
        const newUsername = document.querySelector("#editName").value.trim();
        const newPassword = document.querySelector("#editPassword").value.trim();
        const newRole = document.querySelector("#editRole").value;

        // Validation
        if (!newUsername) {
            showAlert("Username cannot be empty", "error");
            return;
        }

        if (newUsername.length < 3 || newUsername.length > 50) {
            showAlert("Username must be between 3 and 50 characters", "error");
            return;
        }

        if (newPassword && newPassword.length < 6) {
            showAlert("Password must be at least 6 characters", "error");
            return;
        }

        if (!newRole) {
            showAlert("Role must be selected", "error");
            return;
        }

        // Send to server
        const formData = new FormData();
        formData.append("action", "update");
        formData.append("username", newUsername);
        formData.append("password", newPassword);
        formData.append("role", newRole);

        fetch(window.location.href, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the displayed values
                userNameSpan.textContent = data.username;
                userRoleSpan.textContent = data.role;
                showAlert(data.message, "success");
                closeModal();
            } else {
                showAlert(data.message, "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("An error occurred while updating", "error");
        });
    });

    // Display greeting
    const header = document.querySelector(".header");
    if (header) {
        const hour = new Date().getHours();
        let greeting = "Welcome";

        if (hour < 12) greeting = "Good Morning";
        else if (hour < 18) greeting = "Good Afternoon";
        else greeting = "Good Evening";

        header.innerText = `${greeting}`;
    }
});

// ===== Alert Notifications =====
function showAlert(message, type = 'success') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    const content = document.querySelector('.content');
    if (content) {
        content.insertBefore(alert, content.firstChild);
        
        setTimeout(() => {
            alert.remove();
        }, 4000);
    }
}

// ===== Menu Navigation =====
function setupNavigation() {
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
}

// ===== Logout Confirmation =====
function setupLogout() {
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

// Initialize
setupNavigation();
setupLogout();