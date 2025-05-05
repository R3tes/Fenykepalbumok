<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!isset($_POST['kepID'], $_POST['helyID'], $_POST['kategoriaID'])) {
    die("Hiányzó adatok a módosításhoz.");
}

$kepID = intval($_POST['kepID']);
$helyID = intval($_POST['helyID']);
$kategoriaID = intval($_POST['kategoriaID']);

$updateKepStmt = oci_parse($conn, "
    UPDATE Kep 
    SET helyID = :helyID 
    WHERE kepID = :kepID
");
oci_bind_by_name($updateKepStmt, ":helyID", $helyID);
oci_bind_by_name($updateKepStmt, ":kepID", $kepID);
oci_execute($updateKepStmt);

$deleteKatStmt = oci_parse($conn, "
    DELETE FROM KategoriaResze 
    WHERE kepID = :kepID
");
oci_bind_by_name($deleteKatStmt, ":kepID", $kepID);
oci_execute($deleteKatStmt);

$insertKatStmt = oci_parse($conn, "
    INSERT INTO KategoriaResze (katID, kepID) 
    VALUES (:katID, :kepID)
");
oci_bind_by_name($insertKatStmt, ":katID", $kategoriaID);
oci_bind_by_name($insertKatStmt, ":kepID", $kepID);
oci_execute($insertKatStmt);

oci_commit($conn);
header("Location: picture.php?id=" . $kepID);
exit();
?>
