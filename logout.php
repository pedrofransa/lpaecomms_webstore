<?php
session_start();
require_once "includes/functions.php";
lpa_log("Customer logged out.");
session_destroy();
header("Location: index.php");
exit;
?>
