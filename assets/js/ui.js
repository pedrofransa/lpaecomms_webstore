// UI functions for LPA eComms

function toggleTheme() {
    document.body.classList.toggle("dark-theme");

    if (document.body.classList.contains("dark-theme")) {
        localStorage.setItem("lpa_theme", "dark");
    } else {
        localStorage.setItem("lpa_theme", "light");
    }
}

function loadSavedTheme() {
    const savedTheme = localStorage.getItem("lpa_theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
    }
}

function toggleMenu() {
    const menu = document.getElementById("main-menu");

    if (menu) {
        menu.classList.toggle("menu-open");
    }
}

function updatePaymentFields() {
    const paymentOption = document.getElementById("payment_option");
    const cardFields = document.getElementById("card-fields");
    const paypalFields = document.getElementById("paypal-fields");
    const depositFields = document.getElementById("deposit-fields");

    if (!paymentOption || !cardFields || !paypalFields || !depositFields) {
        return;
    }

    cardFields.hidden = !["VISA", "MasterCard"].includes(paymentOption.value);
    paypalFields.hidden = paymentOption.value !== "PayPal";
    depositFields.hidden = paymentOption.value !== "Direct deposit";

    cardFields.querySelectorAll("input").forEach(function (input) {
        input.required = !cardFields.hidden;
    });

    paypalFields.querySelectorAll("input").forEach(function (input) {
        input.required = !paypalFields.hidden;
    });
}

function validateRegisterForm(event) {
    const firstName = document.getElementById("first_name");
    const lastName = document.getElementById("last_name");
    const phone = document.getElementById("phone");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const errorBox = document.getElementById("register_error");

    if (!errorBox) {
        return true;
    }

    errorBox.innerHTML = "";

    if (
        firstName.value.trim() === "" ||
        lastName.value.trim() === "" ||
        username.value.trim() === ""
    ) {
        errorBox.innerHTML = "Please fill in your first name, last name and username.";
        event.preventDefault();
        return false;
    }

    if (phone.value.trim().length < 8) {
        errorBox.innerHTML = "Please enter a valid phone number.";
        event.preventDefault();
        return false;
    }

    if (password.value.length < 6) {
        errorBox.innerHTML = "Password must have at least 6 characters.";
        event.preventDefault();
        return false;
    }

    if (password.value !== confirmPassword.value) {
        errorBox.innerHTML = "Password and Confirm Password do not match.";
        event.preventDefault();
        return false;
    }

    return true;
}

document.addEventListener("DOMContentLoaded", function () {
    loadSavedTheme();
    updatePaymentFields();
});
