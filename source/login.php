<?php
session_start();
include 'resources/SUPPORT_FUNCS/db_connection.php';

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

                $naplo_stmt = oci_parse($conn, "INSERT INTO SessionNaplo (felhasznalo_id, belepes_ideje) VALUES (:fid, SYSDATE)");
                oci_bind_by_name($naplo_stmt, ":fid", $row['FID']);
                if (!oci_execute($naplo_stmt)) {
                    $e = oci_error($naplo_stmt);
                    error_log("SessionNaplo beszúrási hiba: " . $e['message']);
                }
                oci_free_statement($naplo_stmt);

                $_SESSION['success_message'] = "Sikeres bejelentkezés. Üdvözlünk, " . htmlspecialchars($row['FNEV'], ENT_QUOTES, 'UTF-8') . "!";
                header("Location: index.php");
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
    <link rel="stylesheet" href="resources/CSS/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>
<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        alert("<?= addslashes($_SESSION['success_message']) ?>");
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

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
