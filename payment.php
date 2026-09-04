<?php
require_once "config/db.php";
require_once "includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

redirect_if_not_logged_in();

if (empty(get_cart())) {
    header("Location: cart.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lpa_clients WHERE lpa_client_ID = ?");
$stmt->execute([$_SESSION["customer_id"]]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    session_destroy();
    header("Location: login.php");
    exit;
}

include "includes/header.php";
?>

<section class="page-heading compact-heading">
    <span class="eyebrow">Final step</span>
    <h1>Checkout details</h1>
    <p>Confirm your contact details and select a demo payment method.</p>
</section>

<div class="checkout-form">
    <div class="demo-notice"><strong>Student project:</strong> this form demonstrates a checkout interface. No real payment is processed or stored.</div>

    <form method="post" action="complete.php">
        <h2>Contact information</h2>
        <div class="form-grid">
        <div>
        <label>First Name</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($customer["lpa_client_firstname"]); ?>" required>
        </div>

        <div>
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($customer["lpa_client_lastname"]); ?>" required>
        </div>

        <div class="full-field">
        <label>Address</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($customer["lpa_client_address"]); ?>" required>
        </div>

        <div class="full-field">
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($customer["lpa_client_phone"]); ?>" required>
        </div>
        </div>

        <h2>Payment method</h2>
        <label>Payment Option</label>
        <select id="payment_option" name="payment_option" onchange="updatePaymentFields()" required>
            <option value="">Select payment</option>
            <option value="PayPal">PayPal</option>
            <option value="VISA">VISA</option>
            <option value="MasterCard">MasterCard</option>
            <option value="Direct deposit">Direct deposit</option>
        </select>

        <div id="card-fields" class="payment-fields" hidden>
            <label>Cardholder Name</label>
            <input type="text" name="cardholder" autocomplete="off">
            <label>Demo Card Number</label>
            <input type="text" name="card_number" inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="off">
            <div class="form-grid">
                <div><label>Expiry</label><input type="text" name="expiry" maxlength="5" placeholder="MM/YY" autocomplete="off"></div>
                <div><label>CVV</label><input type="password" name="cvv" maxlength="4" placeholder="000" autocomplete="off"></div>
            </div>
        </div>

        <div id="paypal-fields" class="payment-fields" hidden>
            <label>PayPal Email</label>
            <input type="email" name="paypal_email" placeholder="name@example.com" autocomplete="off">
        </div>

        <div id="deposit-fields" class="payment-fields payment-copy" hidden>
            <p>Bank details would normally be displayed after the order is submitted.</p>
        </div>

        <div class="form-actions">
            <button type="submit">Submit demo order</button>
            <a class="button secondary-button" href="cart.php">Back to cart</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
