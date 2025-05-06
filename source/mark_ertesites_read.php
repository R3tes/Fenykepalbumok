<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');

if (!isset($_SESSION['fID']) || !isset($_POST['id'])) {
    http_response_code(400);
    exit('Hiba');
}

$ertesites_id = intval($_POST['id']);
$fID = $_SESSION['fID'];

$stmt = oci_parse($conn, "
    UPDATE Ertesites
    SET olvasott = 1
    WHERE ertesites_id = :id AND felhasznalo_id = :fID
");

oci_bind_by_name($stmt, ":id", $ertesites_id);
oci_bind_by_name($stmt, ":fID", $fID);

if (oci_execute($stmt)) {
    echo "OK";
} else {
    echo "Hiba";
}

oci_free_statement($stmt);
oci_close($conn);
?>
