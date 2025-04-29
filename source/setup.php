<?php
include 'resources/SUPPORT_FUNCS/db_connection.php';

$stmt = oci_parse($conn, "SELECT fID, email, jelszo FROM Felhasznalo");
oci_execute($stmt);

while ($row = oci_fetch_assoc($stmt)) {
    if (!password_verify($row['JELSZO'], $row['JELSZO'])) {
        $hashed_pw = password_hash($row['JELSZO'], PASSWORD_DEFAULT);

        $update_stmt = oci_parse($conn, "UPDATE Felhasznalo SET jelszo = :hashed_pw WHERE fID = :fID");
        oci_bind_by_name($update_stmt, ":hashed_pw", $hashed_pw);
        oci_bind_by_name($update_stmt, ":fID", $row['FID']);
        oci_execute($update_stmt);
    }
}

oci_free_statement($stmt);
oci_close($conn);

echo "Minden felhasználó jelszava sikeresen hashelve lett!";
?>
