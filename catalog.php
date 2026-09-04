<?php
require_once "config/db.php";
require_once "includes/functions.php";

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["stock_id"])) {
    $stock_id = trim($_POST["stock_id"]);
    $stmt = $pdo->prepare("SELECT lpa_stock_ID, lpa_stock_onhand FROM lpa_stock WHERE lpa_stock_ID = ? AND lpa_stock_status = 'E'");
    $stmt->execute([$stock_id]);
    $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$selected_product || (int)$selected_product["lpa_stock_onhand"] < 1) {
        $message = "<div class='error'>This product is not available.</div>";
    } else {
        $cart = get_cart();
        $current_qty = isset($cart[$stock_id]) ? (int)$cart[$stock_id] : 0;

        if ($current_qty >= (int)$selected_product["lpa_stock_onhand"]) {
            $message = "<div class='error'>The selected quantity is not available.</div>";
        } else {
            $cart[$stock_id] = $current_qty + 1;
            save_cart($cart);
            lpa_log("Product added to cart: " . $stock_id);
            $message = "<div class='success'>Product added to cart.</div>";
        }
    }
}

if ($search !== "") {
    $stmt = $pdo->prepare("SELECT * FROM lpa_stock WHERE lpa_stock_status = 'E' AND lpa_stock_name LIKE ?");
    $stmt->execute(["%" . $search . "%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM lpa_stock WHERE lpa_stock_status = 'E'");
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$fallback_images = [
    "STK001" => "wireless-mouse.webp",
    "STK002" => "usb-keyboard.webp",
    "STK003" => "hdmi-cable.webp",
    "STK004" => "laptop-stand.webp"
];

include "includes/header.php";
?>

<section class="page-heading">
    <span class="eyebrow">LPA collection</span>
    <h1>Find the right gear for your setup.</h1>
    <p>Reliable accessories with clear pricing and live stock information.</p>
</section>

<?php echo $message; ?>

<form class="search-bar" method="get">
    <label class="sr-only" for="product-search">Search Products</label>
    <input id="product-search" type="search" name="search" placeholder="Search by product name..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<div class="catalog-grid">
<?php foreach ($products as $product): ?>
    <?php
    $product_image = trim($product["lpa_stock_image"] ?? "");
    if ($product_image === "" && isset($fallback_images[$product["lpa_stock_ID"]])) {
        $product_image = $fallback_images[$product["lpa_stock_ID"]];
    }
    ?>
    <article class="product-card">
        <div class="product-visual">
            <?php if ($product_image !== ""): ?>
                <img src="assets/images/<?php echo htmlspecialchars($product_image); ?>"
                     alt="<?php echo htmlspecialchars($product["lpa_stock_name"]); ?>"
                     loading="lazy">
            <?php else: ?>
                <span><?php echo substr(htmlspecialchars($product["lpa_stock_name"]), 0, 1); ?></span>
            <?php endif; ?>
        </div>
        <div class="product-body">
            <div class="product-meta">
                <span class="stock-code"><?php echo htmlspecialchars($product["lpa_stock_ID"]); ?></span>
                <span class="stock-status"><?php echo (int)$product["lpa_stock_onhand"]; ?> in stock</span>
            </div>
            <h2><?php echo htmlspecialchars($product["lpa_stock_name"]); ?></h2>
            <p><?php echo htmlspecialchars($product["lpa_stock_desc"]); ?></p>
            <div class="product-footer">
                <strong>$<?php echo number_format($product["lpa_stock_price"], 2); ?></strong>
                <form method="post">
                    <input type="hidden" name="stock_id" value="<?php echo htmlspecialchars($product["lpa_stock_ID"]); ?>">
                    <button type="submit" <?php echo (int)$product["lpa_stock_onhand"] < 1 ? "disabled" : ""; ?>>Add to cart</button>
                </form>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>

<?php if (count($products) === 0): ?>
    <div class="empty-state"><h2>No products found</h2><p>Try a different product name.</p></div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
