<?php
require_once "config/db.php";
require_once "includes/functions.php";

$cart = get_cart();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["remove"])) {
        unset($cart[$_POST["remove"]]);
    }

    if (isset($_POST["qty"])) {
        foreach ($_POST["qty"] as $stock_id => $qty) {
            if (!array_key_exists($stock_id, $cart)) {
                continue;
            }

            $stmt = $pdo->prepare("SELECT lpa_stock_onhand FROM lpa_stock WHERE lpa_stock_ID = ? AND lpa_stock_status = 'E'");
            $stmt->execute([$stock_id]);
            $available = $stmt->fetchColumn();

            if ($available !== false && (int)$available > 0) {
                $cart[$stock_id] = min(max(1, (int)$qty), (int)$available);
            } else {
                unset($cart[$stock_id]);
            }
        }
    }

    save_cart($cart);
    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;

if (!empty($cart)) {
    $placeholders = implode(",", array_fill(0, count($cart), "?"));
    $stmt = $pdo->prepare("SELECT * FROM lpa_stock WHERE lpa_stock_ID IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include "includes/header.php";
?>

<section class="page-heading compact-heading">
    <span class="eyebrow">Your order</span>
    <h1>Shopping cart</h1>
    <p>Review your items before continuing to checkout.</p>
</section>

<?php if (empty($items)): ?>
    <div class="empty-state">
        <div class="empty-icon">0</div>
        <h2>Your cart is empty</h2>
        <p>Explore our products and add something to your setup.</p>
        <a class="button primary-button" href="catalog.php">Browse products</a>
    </div>
<?php else: ?>
    <form class="cart-layout" method="post">
        <div class="cart-table-wrap">
        <table class="cart-table">
            <tr>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>QTY</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>

            <?php foreach ($items as $item): ?>
                <?php
                $qty = $cart[$item["lpa_stock_ID"]];
                $amount = $qty * $item["lpa_stock_price"];
                $total += $amount;
                ?>
                <tr>
                    <td><span class="stock-code"><?php echo htmlspecialchars($item["lpa_stock_ID"]); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($item["lpa_stock_name"]); ?></strong></td>
                    <td>$<?php echo number_format($item["lpa_stock_price"], 2); ?></td>
                    <td>
                        <input type="number" name="qty[<?php echo htmlspecialchars($item["lpa_stock_ID"]); ?>]" value="<?php echo $qty; ?>" min="1">
                    </td>
                    <td>$<?php echo number_format($amount, 2); ?></td>
                    <td>
                        <button class="remove-button" type="submit" name="remove" value="<?php echo htmlspecialchars($item["lpa_stock_ID"]); ?>">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>
        <button class="text-button" type="submit">Update quantities</button>
        </div>
        <aside class="order-summary">
            <span class="eyebrow">Order summary</span>
            <div><span>Subtotal</span><strong>$<?php echo number_format($total, 2); ?></strong></div>
            <div><span>Delivery</span><strong>Free</strong></div>
            <div class="summary-total"><span>Total</span><strong>$<?php echo number_format($total, 2); ?></strong></div>
            <a class="button primary-button full-button" href="payment.php">Continue to checkout</a>
            <a class="continue-link" href="catalog.php">Continue shopping</a>
        </aside>
    </form>
<?php endif; ?>

<?php include "includes/footer.php"; ?>
