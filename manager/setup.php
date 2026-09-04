<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "includes/manager_functions.php";

manager_ensure_stock_image_column($pdo);

if (manager_user_count($pdo) > 0) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    manager_check_csrf();
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($first_name === "" || $last_name === "" || $username === "") {
        $message = "<div class='error'>Please complete all fields.</div>";
    } elseif (strlen($password) < 8) {
        $message = "<div class='error'>Password must have at least 8 characters.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='error'>Passwords do not match.</div>";
    } else {
        $user_id = "MGR" . time();
        $stmt = $pdo->prepare("INSERT INTO lpa_users
            (lpa_user_ID, lpa_user_username, lpa_user_password, lpa_user_firstname, lpa_user_lastname, lpa_user_group, lpa_inv_status)
            VALUES (?, ?, ?, ?, ?, 'Manager', 'E')");
        $stmt->execute([$user_id, $username, password_hash($password, PASSWORD_BCRYPT), $first_name, $last_name]);
        lpa_log("First manager account created: " . $username);
        header("Location: login.php?created=1");
        exit;
    }
}

include "includes/manager_header.php";
?>
<div class="form-box manager-login-box">
    <span class="eyebrow">First-time setup</span>
    <h1>Create manager account</h1>
    <p class="form-intro">This page is available only while no active manager account exists.</p>
    <?php echo $message; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(manager_csrf_token()); ?>">
        <div class="form-grid">
            <div><label>First Name</label><input type="text" name="first_name" required></div>
            <div><label>Last Name</label><input type="text" name="last_name" required></div>
        </div>
        <label>Username</label><input type="text" name="username" required>
        <label>Password</label><input type="password" name="password" minlength="8" required>
        <label>Confirm Password</label><input type="password" name="confirm_password" minlength="8" required>
        <button class="full-button manager-submit" type="submit">Create manager account</button>
    </form>
</div>
<?php include "includes/manager_footer.php"; ?>
