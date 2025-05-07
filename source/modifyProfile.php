<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');

if (!isset($_SESSION["fID"])) {
    header("Location: login.php");
}

$stmt = oci_parse($conn, "SELECT fNev, email, jelszo FROM Felhasznalo WHERE fID = :fID");
oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]);
$row = [];
if (oci_execute($stmt)) {
    $row = oci_fetch_assoc($stmt);
} else {
    $e = oci_error($stmt);
    header("Location: login.php?id=" . $_SESSION["fID"]);
}
$hiba = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $uname = $_POST["name"];
    $pwd = $_POST["password"];
    $pwd2 = $_POST["password2"];
    if (strlen($pwd) < 6) {
        $hiba = "A jelszó túl rövid.";

    } else {
        if ($pwd === $pwd2) {
            $pwdHash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = oci_parse($conn, "BEGIN update_user_if_changed(:fID, :fNev, :email, :pwd); END;");
            oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]);
            oci_bind_by_name($stmt, ":fNev", $uname);
            oci_bind_by_name($stmt, ":email", $email);
            oci_bind_by_name($stmt, ":pwd", $pwdHash);

            if (!oci_execute($stmt)) {
                $e = oci_error($stmt);
                echo "Query failed: " . $e['message'];
                die();
            } else {
                $_SESSION['success_message'] = "Profil adatok sikeresen módosítva.";
                header("Location: modifyProfile.php");
                exit();
            }
        } else {
            $hiba = "A jelszavak nem egyeznek";
        }
        if (isset($_POST["deleteConf"]) && isset($_POST["delete"])) {

            $stmt = oci_parse($conn, "SELECT kepNev FROM Kep WHERE fID = :fID");
            oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]);
            if (oci_execute($stmt)) {
                $files = scandir("resources/APP_IMGS/");
                $rows = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $rows[] = $row;
                }
                foreach ($rows as $row) {

                    foreach ($files as $file) {
                        if (fnmatch($row['KEPNEV'] . ".*", $file)) {
                            if (!unlink("resources/APP_IMGS/" . $file)) {
                                echo "Error deleting: $file<br>";
                            }
                        }
                    }
                }
            }

            $stmt = oci_parse($conn, "DELETE FROM Felhasznalo WHERE fID = :fID");
            oci_bind_by_name($stmt, ":fID", $_SESSION["fID"]);
            if (!oci_execute($stmt)) {
                $e = oci_error($stmt);
                echo "Query failed: " . $e['message'];
                die();
            }
            header("Location: login.php");
        }
    }
}
?>
    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profil módosítása</title>
        <link rel="stylesheet" href="resources/CSS/styles.css">
        <link rel="stylesheet" href="resources/CSS/upload.css">
        <link rel="stylesheet" href="resources/CSS/navbar.css">
    </head>
    <body>
    <?php include 'navbar.php'; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            alert("<?= addslashes($_SESSION['success_message']) ?>");
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <main>
        <form method="POST">
            <div class="formHead">
                <h2>Felhasználói adatok módosítása</h2>
            </div>

            <div class="uploadForm">
                <div class="formElement">
                    <label for="name">Felhasználónév:</label>
                    <input id="name" name="name" value="<?php echo $row["FNEV"] ?>" placeholder="Felhasználónév"
                           required>
                    <label for="email">Email cím:</label>
                    <input id="email" name="email" type="email" value="<?php echo $row["EMAIL"] ?>"
                           placeholder="Email cím" required>
                    <label for="password">Jelszó:</label>
                    <input id="password" name="password" type="password" placeholder="Jelszó" required>
                    <label for="password2">Jelszó mégegyszer:</label>
                    <input id="password2" name="password2" type="password" placeholder="Jelszó mégegyszer" required>
                    <?php if (!empty($hiba)) echo "<p style='color:red;'>$hiba</p>"; ?>
                    <button type="submit">Módosítás</button>
                    <div>
                        <input id="deleteConf" name="deleteConf" type="checkbox">
                        <label for="deleteConf">Véglegesen törölni szeretném a fiókom.</label>
                    </div>
                    <button type="submit" name="delete" id="delete">Törlés</button>
                </div>
            </div>
        </form>
    </main>
    </body>
    </html>
<?php
oci_free_statement($stmt);
oci_close($conn);
?>