<?php
session_start();
include 'resources/SUPPORT_FUNCS/db_connection.php';

if (isset($_SESSION['fID'])) {
    $fID = $_SESSION['fID'];

    $stmt = oci_parse($conn, "
        UPDATE SessionNaplo 
        SET kilepes_ideje = SYSDATE 
        WHERE felhasznalo_id = :fid 
        AND kilepes_ideje IS NULL 
        AND belepes_ideje = (
            SELECT MAX(belepes_ideje)
            FROM SessionNaplo
            WHERE felhasznalo_id = :fid2
            AND kilepes_ideje IS NULL
        )
    ");
    oci_bind_by_name($stmt, ":fid", $fID);
    oci_bind_by_name($stmt, ":fid2", $fID);
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        error_log("Kilépés naplózási hiba: " . $e['message']);
    }
    oci_free_statement($stmt);
    oci_close($conn);
}

$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
?>