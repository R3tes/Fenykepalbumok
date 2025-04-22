<?php
session_start();
include('db_connection.php');

$palyazatID = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fIDUpload = $_SESSION['fID'];
    $kepNev = $_POST['name'];
    $kategoria = explode(' ', $_POST['categories']);
    $hely = $_POST['country'] .'--'.$_POST['county'].'--'.$_POST['city'];

    if (!empty($kepNev) && !empty($fIDUpload) && !empty($kategoria)) {
        $stmtID = oci_parse($conn, "SELECT NVL(MAX(kepID), 0) + 1 AS nextID FROM Kep");
        oci_execute($stmtID);
        $rowID = oci_fetch_assoc($stmtID);
        $ujKepID = $rowID['NEXTID'];
        oci_free_statement($stmtID);

        $stmt = oci_parse($conn, "INSERT INTO Kep (kepID, kepNev, fID) VALUES (:kepID, :kepNev , :fID)");
        oci_bind_by_name($stmt, ":kepID", $ujKepID);
        oci_bind_by_name($stmt, ":kepNev", $kepNev);
        oci_bind_by_name($stmt, ":fID", $fIDUpload);

        if (oci_execute($stmt)) {
            if (!empty($_POST['palyazatID'])) {
                $palyazatID = intval($_POST['palyazatID']);

                $stmtNevezett = oci_parse($conn, "INSERT INTO Nevezett (kepID, pID) VALUES (:kepID, :pID)");
                oci_bind_by_name($stmtNevezett, ":kepID", $ujKepID);
                oci_bind_by_name($stmtNevezett, ":pID", $palyazatID);
                oci_execute($stmtNevezett);
                oci_free_statement($stmtNevezett);

                $stmtUser = oci_parse($conn, "SELECT fNev FROM Felhasznalo WHERE fID = :fID");
                oci_bind_by_name($stmtUser, ":fID", $fIDUpload);
                oci_execute($stmtUser);
                $userRow = oci_fetch_assoc($stmtUser);
                $felhasznaloNev = $userRow['FNEV'];
                oci_free_statement($stmtUser);

                $stmtPalyazat = oci_parse($conn, "SELECT palyazatNev FROM Palyazat WHERE pID = :pID");
                oci_bind_by_name($stmtPalyazat, ":pID", $palyazatID);
                oci_execute($stmtPalyazat);
                $palyazatRow = oci_fetch_assoc($stmtPalyazat);
                $palyazatNev = $palyazatRow['PALYAZATNEV'];
                oci_free_statement($stmtPalyazat);

                $_SESSION['success_message'] = "$felhasznaloNev sikeresen jelentkeztél a '$palyazatNev' pályázatra!";

                header("Location: palyazatok.php");
                exit();

            } else {
                $_SESSION['success_message'] = "Kép feltöltve pályázat nélkül.";
            }
        } else {
            $e = oci_error($stmt);
            die("Database Error: " . $e['message']);
        }
        oci_free_statement($stmt);
    }
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <link rel="stylesheet" href="../Web/CSS/upload.css">
</head>
<body>
<main>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="successMessage">
            <?php
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="formHead">
            <h2>Fénykép feltöltése</h2>
            <?php if ($palyazatID): ?>
                <h3>Jelentkezés a pályázatra (ID: <?php echo $palyazatID; ?>)</h3>
                <input type="hidden" name="palyazatID" value="<?php echo $palyazatID; ?>">
            <?php endif; ?>
        </div>

        <div class="uploadForm">
            <div class="formElement">
                <label for="fileInput">Válassza ki a feltöltendő képet:</label>
                <input type="file" id="fileInput" name="uploadedFile" accept="image/*">
            </div>
            <div class="formElement">
                <label for="nameInput">Név:</label>
                <input id="nameInput" name="name">
                <label for="place">Hely:</label>
                <div>
                    <input id="country" name="country">
                    <input id="county" name="county">
                    <input id="city" name="city">
                </div>
                <label for="categoryInput">Kategória:</label>
                <input list="categories" id="categoryInput" name="categories">
                <datalist id="categories">
                    <option value="kat1">
                    <option value="kat2">
                    <option value="kat3">
                </datalist>
                <button type="submit">Feltöltés</button>
            </div>
        </div>
    </form>
</main>
</body>
</html>
