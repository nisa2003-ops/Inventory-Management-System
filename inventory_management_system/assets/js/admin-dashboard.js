// admin-dashbord.js

document.addEventListener("DOMContentLoaded", () => {
    // ===== Sidebar Active Menu =====
    const menuItems = document.querySelectorAll(".menu-item");

    menuItems.forEach((item) => {
        item.addEventListener("click", () => {
            menuItems.forEach((i) => i.classList.remove("active"));
            item.classList.add("active");
        });
    });

    document.querySelectorAll(".menu-item").forEach((item) => {
        if (item.innerText.includes("Customers")) {
            item.addEventListener("click", () => {
                window.location.href = "admin-customers.php";
            });
        }
    });

    document.querySelectorAll(".menu-item").forEach((item) => {
        if (item.innerText.includes("Inventory")) {
            item.addEventListener("click", () => {
                window.location.href = "admin-inventory.php";
            });
        }
    });

    document.querySelectorAll(".menu-item").forEach((item) => {
        if (item.innerText.includes("Suppliers")) {
            item.addEventListener("click", () => {
                window.location.href = "admin-suppliers.php";
            });
        }
    });

    document.querySelectorAll(".menu-item").forEach((item) => {
        if (item.innerText.includes("Settings")) {
            item.addEventListener("click", () => {
                window.location.href = "admin-settings.php";
            });
        }
    });

    // ===== Logout Confirmation =====
    const logoutBtn = document.querySelector(".logout");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            const confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = "index.php"; // change if needed
            }
        });
    }

    // ===== Card Entrance Animation =====
    const cards = document.querySelectorAll(".card");
    cards.forEach((card, index) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        setTimeout(() => {
            card.style.transition = "0.6s ease";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
        }, index * 150);
    });

    // ===== Count Up Animation =====
    const counters = document.querySelectorAll(".card-value");

    counters.forEach((counter) => {
        let targetText = counter.innerText.replace(/[^0-9]/g, "");
        let target = parseInt(targetText);
        let prefix = counter.innerText.replace(/[0-9,]/g, "");
        let count = 0;
        let speed = Math.max(10, target / 100);

        const updateCount = () => {
            if (count < target) {
                count += Math.ceil(speed);
                if (count > target) count = target;
                counter.innerText = prefix + count.toLocaleString();
                requestAnimationFrame(updateCount);
            }
        };
        updateCount();
    });

    // ===== Card Hover Effect =====
    cards.forEach((card) => {
        card.addEventListener("mouseenter", () => {
            card.style.transform = "scale(1.03)";
        });
        card.addEventListener("mouseleave", () => {
            card.style.transform = "scale(1)";
        });
    });

    // ===== Header Greeting Based on Time =====
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