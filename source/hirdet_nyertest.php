<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!$_SESSION['is_admin']) {
    header('Location: palyazatok.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pID'])) {
    $pID = intval($_POST['pID']);

    $query = "SELECT kepID FROM Nevezett WHERE pID = :pID ORDER BY pont DESC FETCH FIRST 1 ROWS ONLY";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":pID", $pID);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if ($row) {
        $kepID = $row['KEPID'];

        $insert = "INSERT INTO Nyertesek (pID, kepID) VALUES (:pID, :kepID)";
        $insertStmt = oci_parse($conn, $insert);
        oci_bind_by_name($insertStmt, ":pID", $pID);
        oci_bind_by_name($insertStmt, ":kepID", $kepID);
        oci_execute($insertStmt);
    }
}

header("Location: palyazatok.php");
exit();
?>
