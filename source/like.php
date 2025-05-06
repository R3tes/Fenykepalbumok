<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_SESSION['fID'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['kepID'])) {
    $fID = $_SESSION['fID'];
    $kepID = intval($_POST['kepID']);

    $stmt = oci_parse($conn, "BEGIN :result := like_kep(:fID, :kepID); END;");

    oci_bind_by_name($stmt, ":fID", $fID, -1, SQLT_INT);
    oci_bind_by_name($stmt, ":kepID", $kepID, -1, SQLT_INT);
    
    $result = null;
    oci_bind_by_name($stmt, ":result", $result, -1, SQLT_INT);
    
    oci_execute($stmt);
}

header("Location: picture.php?id=" . $kepID);
exit();
?>
