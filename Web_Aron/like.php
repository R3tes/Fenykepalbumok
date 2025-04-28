<?php
session_start();
require_once '../web_lara/db_connection.php';

if (!isset($_SESSION['fID'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['kepID'])) {
    $fID = $_SESSION['fID'];
    $kepID = intval($_POST['kepID']);

    $query = "SELECT COUNT(*) AS count FROM Likeok WHERE fID = :fID AND kepID = :kepID";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ":fID", $fID);
    oci_bind_by_name($stid, ":kepID", $kepID);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);

    if ($row['COUNT'] == 0) {

        $insertQuery = "INSERT INTO Likeok (fID, kepID) VALUES (:fID, :kepID)";
        $insertStmt = oci_parse($conn, $insertQuery);
        oci_bind_by_name($insertStmt, ":fID", $fID);
        oci_bind_by_name($insertStmt, ":kepID", $kepID);
        oci_execute($insertStmt);

        $updateQuery = "UPDATE Kep SET ertekeles = ertekeles + 1 WHERE kepID = :kepID";
        $updateStmt = oci_parse($conn, $updateQuery);
        oci_bind_by_name($updateStmt, ":kepID", $kepID);
        oci_execute($updateStmt);
    }
}

header("Location: picture.php?id=" . $kepID);
exit();
?>
