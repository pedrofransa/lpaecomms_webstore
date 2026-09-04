<?php
require_once "../config/db.php";
require_once "includes/manager_functions.php";
manager_require_login();
manager_ensure_stock_image_column($pdo);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_status"])) {
    manager_check_csrf();
    $stock_id = trim($_POST["toggle_status"]);
    $stmt = $pdo->prepare("UPDATE lpa_stock SET lpa_stock_status = IF(lpa_stock_status = 'E', 'D', 'E') WHERE lpa_stock_ID = ?");
    $stmt->execute([$stock_id]);
    manager_set_message("success", "Product status updated.");
    header("Location: index.php");
    exit;
}

$products = $pdo->query("SELECT * FROM lpa_stock ORDER BY lpa_stock_ID")->fetchAll(PDO::FETCH_ASSOC);
$message = manager_get_message();
include "includes/manager_header.php";
?>
<section class="manager-title-row">
    <div><span class="eyebrow">Catalogue control</span><h1>Product management</h1><p>Edit products, prices, stock and catalogue images.</p></div>
    <a class="button manager-add-button" href="product.php">+ Add product</a>
</section>

<?php if ($message): ?>
    <div class="<?php echo $message["type"] === "error" ? "error" : "success"; ?>"><?php echo htmlspecialchars($message["text"]); ?></div>
<?php endif; ?>

<div class="manager-product-grid">
<?php foreach ($products as $product): ?>
    <article class="manager-product-card">
        <div class="manager-product-image">
            <?php if (!empty($product["lpa_stock_image"])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($product["lpa_stock_image"]); ?>" alt="">
            <?php else: ?><span>No image</span><?php endif; ?>
        </div>
        <div class="manager-product-body">
            <div class="product-meta">
                <span class="stock-code"><?php echo htmlspecialchars($product["lpa_stock_ID"]); ?></span>
                <span class="manager-status <?php echo $product["lpa_stock_status"] === "E" ? "enabled" : "disabled"; ?>">
                    <?php echo $product["lpa_stock_status"] === "E" ? "Enabled" : "Disabled"; ?>
                </span>
            </div>
            <h2><?php echo htmlspecialchars($product["lpa_stock_name"]); ?></h2>
            <p><?php echo htmlspecialchars($product["lpa_stock_desc"]); ?></p>
            <div class="manager-stats"><strong>$<?php echo number_format($product["lpa_stock_price"], 2); ?></strong><span><?php echo (int)$product["lpa_stock_onhand"]; ?> in stock</span></div>
            <div class="manager-card-actions">
                <a class="button" href="product.php?id=<?php echo urlencode($product["lpa_stock_ID"]); ?>">Edit product</a>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(manager_csrf_token()); ?>">
                    <button class="secondary-button" type="submit" name="toggle_status" value="<?php echo htmlspecialchars($product["lpa_stock_ID"]); ?>">
                        <?php echo $product["lpa_stock_status"] === "E" ? "Disable" : "Enable"; ?>
                    </button>
                </form>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php include "includes/manager_footer.php"; ?>
