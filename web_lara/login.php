<?php
session_start();
include 'db_connection.php';

if (isset($_SESSION['success_message'])) {
    echo "<p style='color:green; text-align:center;'>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']);
}

$hiba = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $jelszo = $_POST['jelszo'];

    if (!empty($email) && !empty($jelszo)) {
        $stmt = oci_parse($conn, "SELECT fID, fNev, jelszo, jogosultsag FROM Felhasznalo WHERE email = :email");
        oci_bind_by_name($stmt, ":email", $email);
        oci_execute($stmt);

        if ($row = oci_fetch_assoc($stmt)) {
            if (password_verify($jelszo, $row['JELSZO'])) {
                $_SESSION['fID'] = $row['FID'];
                $_SESSION['fNev'] = $row['FNEV'];
                $_SESSION['jogosultsag'] = $row['JOGOSULTSAG'];
                $_SESSION['is_admin'] = $row['JOGOSULTSAG'] === 'admin';
                $_SESSION['user_id'] = $email;

                $_SESSION['login_success'] = "Sikeres bejelentkezés. Üdvözlünk, " . htmlspecialchars($row['FNEV']) . "!";

                header("Location: palyazatok.php");
                exit;
            } else {
                $hiba = "Hibás email cím vagy jelszó.";
            }
        } else {
            $hiba = "Hibás email cím vagy jelszó.";
        }

        oci_free_statement($stmt);
        oci_close($conn);
    } else {
        $hiba = "Kérlek, töltsd ki az összes mezőt.";
    }
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
    <?php if (!empty($hiba)) echo "<p style='color:red;'>$hiba</p>"; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="jelszo" placeholder="Jelszó" required>
    <input type="submit" value="Bejelentkezés">
    <p style="text-align:center;"><a href="register.php">Nincs még fiókod? Regisztrálj!</a></p>
</form>
</body>
</html>
