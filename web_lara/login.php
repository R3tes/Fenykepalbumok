<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $jelszo = $_POST['jelszo'];

    $stmt = oci_parse($conn, "SELECT fID, fNev, jelszo, jogosultsag FROM Felhasznalo WHERE email = :email");
    oci_bind_by_name($stmt, ":email", $email);
    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        if (password_verify($jelszo, $row['JELSZO'])) {
            $_SESSION['fID'] = $row['FID'];
            $_SESSION['fNev'] = $row['FNEV'];
            $_SESSION['jogosultsag'] = $row['JOGOSULTSAG'];

            // Admin jogosultság beállítása
            $_SESSION['is_admin'] = $row['JOGOSULTSAG'] === 'admin'; // Ha 'admin' jogosultsággal rendelkezik, akkor igaz
            $_SESSION['user_id'] = $email;

            header("Location: palyazatok.php");
            exit;
        } else {
            $hiba = "Hibás jelszó!";
        }
    } else {
        $hiba = "Nem található felhasználó ezzel az email címmel.";
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form method="POST">
    <h2>Bejelentkezés</h2>
    <?php if (isset($hiba)) echo "<p style='color:red;'>$hiba</p>"; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="jelszo" placeholder="Jelszó" required>
    <input type="submit" value="Bejelentkezés">
    <p style="text-align:center;"><a href="register.php">Nincs még fiókod? Regisztrálj!</a></p>
</form>
</body>
</html>
