<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/functions.php";
$current_page = basename($_SERVER["PHP_SELF"]);
$cart_count = cart_item_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LPA eComms</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
    <script src="assets/js/ui.js?v=3" defer></script>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php" aria-label="LPA eComms home">
            <span class="brand-mark">LPA</span>
            <span class="brand-copy">
                <strong>LPA eComms</strong>
                <small>Technology made simple</small>
            </span>
        </a>

        <button type="button" class="mobile-menu-button" onclick="toggleMenu()" aria-label="Open menu">Menu</button>

        <nav class="menu" id="main-menu">
            <a class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
            <a class="<?php echo $current_page === 'catalog.php' ? 'active' : ''; ?>" href="catalog.php">Products</a>
            <a class="cart-link <?php echo in_array($current_page, ['cart.php', 'payment.php', 'complete.php']) ? 'active' : ''; ?>" href="cart.php">
                Cart <span class="cart-count"><?php echo $cart_count; ?></span>
            </a>
            <a class="<?php echo $current_page === 'mashup.php' ? 'active' : ''; ?>" href="mashup.php">About</a>
            <?php if (is_logged_in()): ?>
                <span class="welcome-name">Hi, <?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="login.php">Login</a>
                <a class="nav-cta <?php echo $current_page === 'register.php' ? 'active' : ''; ?>" href="register.php">Create account</a>
            <?php endif; ?>
            <button type="button" class="theme-button" onclick="toggleTheme()" aria-label="Change colour theme">
                <span class="theme-icon">☾</span>
            </button>
        </nav>
    </div>
</header>

<main class="content">
