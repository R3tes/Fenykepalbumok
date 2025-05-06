<?php
session_start();
require_once 'resources/SUPPORT_FUNCS/db_connection.php';

$kepID = $_POST['kepID'] ?? null;
$orszag = trim($_POST['orszag'] ?? '');
$megye = trim($_POST['megye'] ?? '');
$varos = trim($_POST['varos'] ?? '');
$kategoriNev = trim($_POST['category'] ?? '');

if (!$kepID) {
    die("Hiányzó képazonosító.");
}

function oci_seq_nextval($conn, $seqName) {
    $stid = oci_parse($conn, "SELECT {$seqName}.NEXTVAL AS VAL FROM dual");
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    return $row['VAL'];
}

// HelyID lekérdezés vagy beszúrás
$helyID = null;
if ($orszag && $megye && $varos) {
    $stmt = oci_parse($conn, "
        SELECT helyID FROM Hely WHERE LOWER(orszag) = LOWER(:orszag) AND LOWER(megye) = LOWER(:megye) AND LOWER(varos) = LOWER(:varos)
    ");
    oci_bind_by_name($stmt, ":orszag", $orszag);
    oci_bind_by_name($stmt, ":megye", $megye);
    oci_bind_by_name($stmt, ":varos", $varos);
    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        $helyID = $row['HELYID'];
    } else {
        // új hely beszúrása
        $helyID = oci_seq_nextval($conn, 'HELY_SEQ'); // segédfüggvény
        $stmt = oci_parse($conn, "INSERT INTO Hely (helyID, orszag, megye, varos) VALUES (:helyID, :orszag, :megye, :varos)");
        oci_bind_by_name($stmt, ":helyID", $helyID);
        oci_bind_by_name($stmt, ":orszag", $orszag);
        oci_bind_by_name($stmt, ":megye", $megye);
        oci_bind_by_name($stmt, ":varos", $varos);
        oci_execute($stmt);
    }

    $stmt = oci_parse($conn, "UPDATE Kep SET helyID = :helyID WHERE kepID = :kepID");
    oci_bind_by_name($stmt, ":helyID", $helyID);
    oci_bind_by_name($stmt, ":kepID", $kepID);
    oci_execute($stmt);
}

// Kategória frissítése, ha van
if (!empty($kategoriNev)) {
    $katID = null;

    $stmt = oci_parse($conn, "SELECT katID FROM Kategoria WHERE LOWER(kategoriaNev) = LOWER(:nev)");
    oci_bind_by_name($stmt, ":nev", $kategoriNev);
    oci_execute($stmt);
    if ($row = oci_fetch_assoc($stmt)) {
        $katID = $row['KATID'];
    } else {
        // új kategória beszúrása
        $katID = oci_seq_nextval($conn, 'KAT_SEQ'); // segédfüggvény
        $stmt = oci_parse($conn, "INSERT INTO Kategoria (katID, kategoriaNev) VALUES (:katID, :nev)");
        oci_bind_by_name($stmt, ":katID", $katID);
        oci_bind_by_name($stmt, ":nev", $kategoriNev);
        oci_execute($stmt);
    }

    // először törölni a korábbi kapcsolódást
    $stmt = oci_parse($conn, "DELETE FROM KategoriaResze WHERE kepID = :kepID");
    oci_bind_by_name($stmt, ":kepID", $kepID);
    oci_execute($stmt);

    // új kapcsolat
    $stmt = oci_parse($conn, "INSERT INTO KategoriaResze (kepID, katID) VALUES (:kepID, :katID)");
    oci_bind_by_name($stmt, ":kepID", $kepID);
    oci_bind_by_name($stmt, ":katID", $katID);
    oci_execute($stmt);
}

oci_close($conn);
header("Location: picture.php?id=" . $kepID);
exit();
?>
