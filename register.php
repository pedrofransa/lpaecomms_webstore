<?php
require_once "config/db.php";
require_once "includes/functions.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $address = trim($_POST["address"]);
    $phone = trim($_POST["phone"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $message = "<div class='error'>Password and Confirm Password do not match.</div>";
        lpa_log("Customer registration failed: password mismatch for username " . $username);
    } elseif (strlen($password) < 6) {
        $message = "<div class='error'>Password must have at least 6 characters.</div>";
        lpa_log("Customer registration failed: short password for username " . $username);
    } elseif (strlen($phone) < 8) {
        $message = "<div class='error'>Please enter a valid phone number.</div>";
        lpa_log("Customer registration failed: invalid phone for username " . $username);
    } else {
        $stmt = $pdo->prepare("SELECT lpa_client_ID FROM lpa_clients WHERE lpa_client_username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $message = "<div class='error'>This username is already registered.</div>";
            lpa_log("Customer registration failed: duplicate username " . $username);
        } else {
            $customer_id = "CUST" . time();
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO lpa_clients 
                    (lpa_client_ID, lpa_client_firstname, lpa_client_lastname, lpa_client_address, lpa_client_phone, lpa_client_status, lpa_client_username, lpa_client_password)
                    VALUES (?, ?, ?, ?, ?, 'E', ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $customer_id,
                $first_name,
                $last_name,
                $address,
                $phone,
                $username,
                $hashed_password
            ]);

            $message = "<div class='success'>Registration completed successfully.</div>";
            lpa_log("New customer registered: " . $username);
        }
    }
}

include "includes/header.php";
?>

<div class="form-box">
    <span class="eyebrow">Join LPA</span>
    <h1>Create your account</h1>
    <p class="form-intro">Enter your details to save your information and complete orders.</p>

    <?php echo $message; ?>

    <form method="post" onsubmit="return validateRegisterForm(event);">
        <div id="register_error" class="error-message"></div>

        <label>First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label>Last Name</label>
        <input type="text" id="last_name" name="last_name" required>

        <label>Address</label>
        <input type="text" id="address" name="address" required>

        <label>Phone Number</label>
        <input type="text" id="phone" name="phone" required>

        <label>Username</label>
        <input type="text" id="username" name="username" required>

        <label>Password</label>
        <input type="password" id="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div class="form-actions">
            <button type="submit">Create account</button>
            <a class="button secondary-button" href="index.php">Cancel</a>
        </div>
    </form>
    <p class="form-footer">Already registered? <a href="login.php">Sign in</a></p>
</div>

<?php include "includes/footer.php"; ?>
