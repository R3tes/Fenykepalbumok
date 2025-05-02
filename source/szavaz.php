<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_SESSION['fID']) || !isset($_POST['kepID']) || !isset($_POST['pID'])) {
    header('Location: palyazatok.php');
    exit();
}

$fID = $_SESSION['fID'];
$kepID = intval($_POST['kepID']);
$pID = intval($_POST['pID']);

$query = "SELECT COUNT(*) AS CNT FROM Szavazatok WHERE fID = :fID AND kepID = :kepID AND pID = :pID";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":fID", $fID);
oci_bind_by_name($stmt, ":kepID", $kepID);
oci_bind_by_name($stmt, ":pID", $pID);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);

if ($row['CNT'] == 0) {
    $insert = "INSERT INTO Szavazatok (fID, kepID, pID) VALUES (:fID, :kepID, :pID)";
    $ins_stmt = oci_parse($conn, $insert);
    oci_bind_by_name($ins_stmt, ":fID", $fID);
    oci_bind_by_name($ins_stmt, ":kepID", $kepID);
    oci_bind_by_name($ins_stmt, ":pID", $pID);
    oci_execute($ins_stmt);

    $update = "UPDATE Nevezett SET pont = pont + 1 WHERE kepID = :kepID AND pID = :pID";
    $upd_stmt = oci_parse($conn, $update);
    oci_bind_by_name($upd_stmt, ":kepID", $kepID);
    oci_bind_by_name($upd_stmt, ":pID", $pID);
    oci_execute($upd_stmt);
}

oci_free_statement($stmt);
oci_close($conn);

$_SESSION['success_message'] = "Sikeres szavazás!";
header("Location: palyazat_kepek.php?id=$pID");
exit();
