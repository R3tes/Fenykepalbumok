<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: ../../web_lara/login.php");
exit;
?>