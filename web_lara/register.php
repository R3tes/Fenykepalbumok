<?php
session_start();
include 'db_connection.php';

$hiba = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nev = trim($_POST['felhasznalonev']);
    $email = trim($_POST['email']);
    $jelszo = $_POST['jelszo'];
    $jelszo2 = $_POST['jelszo2'];

    if (empty($nev) || empty($email) || empty($jelszo) || empty($jelszo2)) {
        $hiba = "Minden mező kitöltése kötelező!";
    } elseif ($jelszo !== $jelszo2) {
        $hiba = "A jelszavak nem egyeznek!";
    } else {
        $ellenor = oci_parse($conn, "SELECT * FROM Felhasznalo WHERE email = :email");
        oci_bind_by_name($ellenor, ":email", $email);
        oci_execute($ellenor);

        if (oci_fetch($ellenor)) {
            $hiba = "Ez az e-mail már foglalt!";
        } else {
            $hashed_pw = password_hash($jelszo, PASSWORD_DEFAULT);

            $stmt = oci_parse($conn, "INSERT INTO Felhasznalo (fID, fNev, email, jelszo, jogosultsag)
                                      VALUES (felhasznalo_seq.NEXTVAL, :nev, :email, :jelszo, 'felhasznalo')"); // Ha nem admin kell

            oci_bind_by_name($stmt, ":nev", $nev);
            oci_bind_by_name($stmt, ":email", $email);
            oci_bind_by_name($stmt, ":jelszo", $hashed_pw);

            if (oci_execute($stmt)) {
                $_SESSION['success_message'] = "Sikeres regisztráció! Most már bejelentkezhetsz.";
                header("Location: login.php");
                exit;
            } else {
                $e = oci_error($stmt);
                $hiba = "Hiba a regisztráció során: " . $e['message'];
            }
            oci_free_statement($stmt);
        }
        oci_free_statement($ellenor);
        oci_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form method="POST">
    <h2>Regisztráció</h2>
    <?php if (!empty($hiba)) echo "<p style='color:red;'>$hiba</p>"; ?>
    <input type="text" name="felhasznalonev" placeholder="Felhasználónév" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="jelszo" placeholder="Jelszó" required>
    <input type="password" name="jelszo2" placeholder="Jelszó újra" required>
    <input type="submit" value="Regisztráció">
    <p style="text-align:center;"><a href="login.php">Már van fiókod? Jelentkezz be!</a></p>
</form>
</body>
</html>
