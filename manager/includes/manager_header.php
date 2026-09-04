<?php
$manager_page = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPA Manager</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=3">
    <script src="../assets/js/ui.js?v=3" defer></script>
</head>
<body>
<header class="manager-header">
    <div class="manager-header-inner">
        <a class="brand" href="index.php">
            <span class="brand-mark">LPA</span>
            <span class="brand-copy"><strong>Manager Portal</strong><small>Store administration</small></span>
        </a>
        <?php if (manager_is_logged_in()): ?>
            <nav class="manager-menu">
                <a class="<?php echo $manager_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Products</a>
                <a href="../index.php" target="_blank">View store</a>
                <span><?php echo htmlspecialchars($_SESSION["manager_name"]); ?></span>
                <a href="logout.php">Logout</a>
                <button type="button" class="theme-button" onclick="toggleTheme()">☾</button>
            </nav>
        <?php endif; ?>
    </div>
</header>
<main class="manager-content">
