<?php
require_once "config/db.php";
require_once "includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

redirect_if_not_logged_in();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$cart = get_cart();

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$first_name = trim($_POST["first_name"] ?? "");
$last_name = trim($_POST["last_name"] ?? "");
$address = trim($_POST["address"] ?? "");
$payment_option = $_POST["payment_option"] ?? "";
$allowed_payment_options = ["PayPal", "VISA", "MasterCard", "Direct deposit"];

if ($first_name === "" || $last_name === "" || $address === "" || !in_array($payment_option, $allowed_payment_options, true)) {
    header("Location: payment.php");
    exit;
}

$placeholders = implode(",", array_fill(0, count($cart), "?"));
$stmt = $pdo->prepare("SELECT * FROM lpa_stock WHERE lpa_stock_ID IN ($placeholders)");
$stmt->execute(array_keys($cart));
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($items) !== count($cart)) {
    header("Location: cart.php");
    exit;
}

$total = 0;
foreach ($items as $item) {
    $qty = (int)$cart[$item["lpa_stock_ID"]];

    if ($item["lpa_stock_status"] !== "E" || $qty < 1 || $qty > (int)$item["lpa_stock_onhand"]) {
        header("Location: cart.php");
        exit;
    }

    $total += $qty * $item["lpa_stock_price"];
}

$invoice_no = "INV" . time();
$client_id = $_SESSION["customer_id"];
$client_name = $first_name . " " . $last_name;
$client_address = $address;

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("INSERT INTO lpa_invoices 
        (lpa_inv_no, lpa_inv_date, lpa_inv_client_ID, lpa_inv_client_name, lpa_inv_client_address, lpa_inv_amount, lpa_inv_status)
        VALUES (?, NOW(), ?, ?, ?, ?, 'P')");
    $stmt->execute([$invoice_no, $client_id, $client_name, $client_address, $total]);

    foreach ($items as $item) {
        $qty = (int)$cart[$item["lpa_stock_ID"]];
        $amount = $qty * $item["lpa_stock_price"];
        $invoice_item_no = "ITEM" . uniqid();

        $stmt = $pdo->prepare("INSERT INTO lpa_invoice_items
            (lpa_invitem_no, lpa_invitem_inv_no, lpa_invitem_stock_ID, lpa_invitem_stock_name, lpa_invitem_qty, lpa_invitem_stock_price, lpa_invitem_stock_amount, lpa_inv_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'P')");
        $stmt->execute([
            $invoice_item_no,
            $invoice_no,
            $item["lpa_stock_ID"],
            $item["lpa_stock_name"],
            $qty,
            $item["lpa_stock_price"],
            $amount
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    lpa_log("Checkout failed: " . $e->getMessage());
    die("The order could not be completed. Please try again.");
}

clear_cart();
lpa_log("Checkout completed. Invoice created: " . $invoice_no);

include "includes/header.php";
?>

<div class="success">
    <span class="success-icon">✓</span>
    <span class="eyebrow">Order received</span>
    <h1>Checkout complete</h1>
    <p>The order was submitted successfully.</p>
    <p class="invoice-number">Invoice number <strong><?php echo htmlspecialchars($invoice_no); ?></strong></p>
    <a class="button primary-button" href="catalog.php">Continue shopping</a>
</div>

<?php include "includes/footer.php"; ?>
