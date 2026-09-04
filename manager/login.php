<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "includes/manager_functions.php";

manager_ensure_stock_image_column($pdo);

if (manager_user_count($pdo) === 0) {
    header("Location: setup.php");
    exit;
}

if (manager_is_logged_in()) {
    header("Location: index.php");
    exit;
}

$message = isset($_GET["created"]) ? "<div class='success'>Manager account created. You can now sign in.</div>" : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    manager_check_csrf();
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $stmt = $pdo->prepare("SELECT * FROM lpa_users WHERE lpa_user_username = ? AND lpa_user_group = 'Manager' AND lpa_inv_status = 'E'");
    $stmt->execute([$username]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($manager && password_verify($password, $manager["lpa_user_password"])) {
        session_regenerate_id(true);
        $_SESSION["manager_id"] = $manager["lpa_user_ID"];
        $_SESSION["manager_name"] = $manager["lpa_user_firstname"] . " " . $manager["lpa_user_lastname"];
        lpa_log("Manager login successful: " . $username);
        header("Location: index.php");
        exit;
    }

    $message = "<div class='error'>Invalid manager username or password.</div>";
    lpa_log("Manager login failed: " . $username);
}

include "includes/manager_header.php";
?>
<div class="form-box manager-login-box">
    <span class="eyebrow">Protected access</span>
    <h1>Manager login</h1>
    <p class="form-intro">Sign in to manage the LPA product catalogue.</p>
    <?php echo $message; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(manager_csrf_token()); ?>">
        <label>Username</label><input type="text" name="username" autocomplete="username" required>
        <label>Password</label><input type="password" name="password" autocomplete="current-password" required>
        <button class="full-button manager-submit" type="submit">Sign in to dashboard</button>
    </form>
    <p class="form-footer"><a href="../index.php">Return to webstore</a></p>
</div>
<?php include "includes/manager_footer.php"; ?>
