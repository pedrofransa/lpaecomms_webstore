<?php
require_once "config/db.php";
require_once "includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM lpa_clients WHERE lpa_client_username = ? AND lpa_client_status = 'E'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && password_verify($password, $customer["lpa_client_password"])) {
        $_SESSION["customer_id"] = $customer["lpa_client_ID"];
        $_SESSION["customer_name"] = $customer["lpa_client_firstname"] . " " . $customer["lpa_client_lastname"];
        lpa_log("Customer login successful: " . $username);
        header("Location: index.php");
        exit;
    } else {
        $message = "<div class='error'>Invalid username or password.</div>";
        lpa_log("Customer login failed for username: " . $username);
    }
}

include "includes/header.php";
?>

<div class="form-box">
    <span class="eyebrow">Welcome back</span>
    <h1>Customer login</h1>
    <p class="form-intro">Sign in to continue with your LPA order.</p>
    <?php echo $message; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="full-button" type="submit">Login</button>
    </form>
    <p class="form-footer">New to LPA? <a href="register.php">Create an account</a></p>
</div>

<?php include "includes/footer.php"; ?>
