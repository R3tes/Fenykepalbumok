<?php
include('resources/SUPPORT_FUNCS/db_connection.php');
session_start();
if (!$_SESSION['is_admin']) {
    header("Location: palyazatok.php");
    exit();
}

$pID = isset($_GET['id']) ? $_GET['id'] : null;

if ($pID) {
    $query = "DELETE FROM Palyazat WHERE pID = :pid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':pid', $pID);
    oci_execute($stmt);
}

$_SESSION['success_message'] = "A pályázat sikeresen törölve.";
header("Location: palyazatok.php");
exit();
?>
