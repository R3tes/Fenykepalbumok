<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

if (!$_SESSION['is_admin']) {
    header('Location: palyazatok.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pID'])) {
    $pID = intval($_POST['pID']);

    // 1. Legmagasabb pontszámú kép lekérése
    $query = "SELECT kepID FROM Nevezett WHERE pID = :pID ORDER BY pont DESC FETCH FIRST 1 ROWS ONLY";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":pID", $pID);
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        die("Hiba a lekérdezés során: " . $e['message']);
    }

    $row = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);

    if ($row) {
        $kepID = $row['KEPID'];

        $insert = "INSERT INTO Nyertesek (pID, kepID) VALUES (:pID, :kepID)";
        $insertStmt = oci_parse($conn, $insert);
        oci_bind_by_name($insertStmt, ":pID", $pID);
        oci_bind_by_name($insertStmt, ":kepID", $kepID);

        if (!oci_execute($insertStmt)) {
            $e = oci_error($insertStmt);
            die("Hiba beszúrás közben: " . $e['message']);
        }

        oci_free_statement($insertStmt);
    } else {
        $_SESSION['error_message'] = "Nincs érvényes nevezés ehhez a pályázathoz.";
    }
}

oci_close($conn);
header("Location: palyazatok.php");
exit();
