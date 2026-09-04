<?php
// Common functions for LPA eComms

function lpa_log($message) {
    $log_directory = __DIR__ . "/../log";

    if (!file_exists($log_directory)) {
        mkdir($log_directory, 0777, true);
    }

    $log_file = $log_directory . "/lpalog.log";
    $date = date("Y-m-d H:i:s");
    $line = "[" . $date . "] " . $message . PHP_EOL;

    file_put_contents($log_file, $line, FILE_APPEND);
}

function is_logged_in() {
    return isset($_SESSION["customer_id"]);
}

function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function get_cart() {
    if (!isset($_COOKIE["lpa_cart"])) {
        return [];
    }

    $cart = json_decode($_COOKIE["lpa_cart"], true);
    return is_array($cart) ? $cart : [];
}

function save_cart($cart) {
    $cart_json = json_encode($cart);
    setcookie("lpa_cart", $cart_json, time() + 86400, "/");
    $_COOKIE["lpa_cart"] = $cart_json;
}

function clear_cart() {
    setcookie("lpa_cart", "", time() - 3600, "/");
    unset($_COOKIE["lpa_cart"]);
}

function cart_item_count() {
    return array_sum(array_map("intval", get_cart()));
}
?>
