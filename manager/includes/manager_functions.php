<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function manager_is_logged_in() {
    return isset($_SESSION["manager_id"]);
}

function manager_require_login() {
    if (!manager_is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function manager_csrf_token() {
    if (empty($_SESSION["manager_csrf"])) {
        $_SESSION["manager_csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["manager_csrf"];
}

function manager_check_csrf() {
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["manager_csrf"] ?? "", $token)) {
        http_response_code(403);
        die("Invalid form request.");
    }
}

function manager_set_message($type, $text) {
    $_SESSION["manager_message"] = ["type" => $type, "text" => $text];
}

function manager_get_message() {
    $message = $_SESSION["manager_message"] ?? null;
    unset($_SESSION["manager_message"]);
    return $message;
}

function manager_user_count($pdo) {
    return (int)$pdo->query("SELECT COUNT(*) FROM lpa_users WHERE lpa_inv_status = 'E'")->fetchColumn();
}

function manager_ensure_stock_image_column($pdo) {
    $stmt = $pdo->query("SHOW COLUMNS FROM lpa_stock LIKE 'lpa_stock_image'");

    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE lpa_stock ADD COLUMN lpa_stock_image VARCHAR(255) NULL AFTER lpa_stock_status");
        $images = [
            "STK001" => "wireless-mouse.webp",
            "STK002" => "usb-keyboard.webp",
            "STK003" => "hdmi-cable.webp",
            "STK004" => "laptop-stand.webp"
        ];
        $update = $pdo->prepare("UPDATE lpa_stock SET lpa_stock_image = ? WHERE lpa_stock_ID = ?");

        foreach ($images as $stock_id => $image) {
            $update->execute([$image, $stock_id]);
        }
    }
}
?>
