<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_SESSION['fID']) || !isset($_POST['kepID'])) {
    header("Location: index.php");
    exit();
}

$fID = intval($_SESSION['fID']);
$kepID = intval($_POST['kepID']);

// Meghívjuk az adatbázisban létrehozott like_pic eljárást
$sql = "BEGIN like_pic(:fid, :kepid); END;";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':fid', $fID);
oci_bind_by_name($stmt, ':kepid', $kepID);

if (oci_execute($stmt)) {
    // Sikeres like után visszairányítjuk a felhasználót a kép oldalra
    header("Location: picture.php?id=$kepID");
} else {
    $e = oci_error($stmt);
    echo "Hiba történt a kedvelés során: " . htmlentities($e['message']);
}

oci_free_statement($stmt);
oci_close($conn);
?>
