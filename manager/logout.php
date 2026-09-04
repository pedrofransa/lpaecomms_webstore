<?php
require_once "../includes/functions.php";
require_once "includes/manager_functions.php";
lpa_log("Manager logged out: " . ($_SESSION["manager_id"] ?? "unknown"));
unset($_SESSION["manager_id"], $_SESSION["manager_name"], $_SESSION["manager_csrf"]);
header("Location: login.php");
exit;
?>
