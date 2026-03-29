document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const usernameInput = form.querySelector("input[type='text']");
    const passwordInput = form.querySelector("input[type='password']");
    const button = form.querySelector(".login-btn");

    // Create message element
    const message = document.createElement("div");
    message.style.marginTop = "15px";
    message.style.textAlign = "center";
    message.style.fontSize = "14px";
    form.appendChild(message);

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        if (username === "" || password === "") {
            showMessage("Please fill all fields", "red");
            return;
        }

        // Fake login data (replace later with backend)
        const users = [
            { user: "admin", pass: "1234", redirect: "admin.html" },
            { user: "manager", pass: "1234", redirect: "manager.html" },
            { user: "cashier", pass: "1234", redirect: "cashier.html" }
        ];

        const found = users.find(
            u => u.user === username && u.pass === password
        );

        button.innerText = "Logging in...";
        button.disabled = true;

        setTimeout(() => {
            if (found) {
                showMessage("Login successful!", "green");
                window.location.href = found.redirect;
            } else {
                showMessage("Invalid username or password", "red");
                button.innerText = "Login";
                button.disabled = false;
            }
        }, 1000);
    });

    function showMessage(text, color) {
        message.innerText = text;
        message.style.color = color;
    }
});