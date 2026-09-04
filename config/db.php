<?php
// Database connection file for LPA eComms
$host = "localhost";
$dbname = "LPA_eComms";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    require_once __DIR__ . "/../includes/functions.php";
    lpa_log("Database connection error: " . $e->getMessage());
    die("Database connection failed.");
}
?>
