<?php
session_start();
require_once '../web_lara/db_connection.php';

if (!isset($_POST['kepID']) || !isset($_POST['comment'])) {
    die('Hibás kérés.');
}

$kepID = intval($_POST['kepID']);
$comment = trim($_POST['comment']);

if (!isset($_SESSION['fID'])) {
    die('Csak bejelentkezett felhasználók kommentelhetnek.');
}

$fID = $_SESSION['fID'];

$query = "INSERT INTO Hozzaszolas (hozzaszolasID, tartalom, fID, kepID)
          VALUES (hozzaszolas_seq.NEXTVAL, :tartalom, :fID, :kepID)";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":tartalom", $comment);
oci_bind_by_name($stmt, ":fID", $fID);
oci_bind_by_name($stmt, ":kepID", $kepID);

if (oci_execute($stmt)) {
    header("Location: picture.php?id=" . $kepID);
    exit();
} else {
    echo "Hiba történt a komment mentése során.";
}
?>
