<?php
session_start();
include('resources/SUPPORT_FUNCS/db_connection.php');

$pID = $_GET['id'] ?? null;
$palyazatNev = "";

$stmt = oci_parse($conn, "SELECT palyazatNev FROM Palyazat WHERE pID = :pID");
oci_bind_by_name($stmt, ":pID", $pID);
oci_execute($stmt);
if ($row = oci_fetch_assoc($stmt)) {
    $palyazatNev = $row['PALYAZATNEV'];
}
oci_free_statement($stmt);

// Datalist előkészítése
$countries = $counties = $cities = $categories = [];

foreach (['orszag' => &$countries, 'megye' => &$counties, 'varos' => &$cities] as $column => &$list) {
    $query = "SELECT DISTINCT $column FROM Hely";
    $stid = oci_parse($conn, $query);
    oci_execute($stid);
    while ($row = oci_fetch_assoc($stid)) {
        $list[] = $row[strtoupper($column)];
    }
}
$stid = oci_parse($conn, "SELECT DISTINCT kategoriaNev FROM Kategoria");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $categories[] = $row['KATEGORIANEV'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fIDUpload = $_SESSION['fID'];
    $kepNev = $_POST['name'] ?? '';
    $orszag = $_POST['countries'] ?? '';
    $megye = $_POST['counties'] ?? '';
    $varos = $_POST['cities'] ?? '';
    $kategoriakInput = $_POST['categories'] ?? '';
    $kategoriak = array_filter(explode(' ', trim($kategoriakInput)));

    // Feltöltés előtt ellenőrzés
    $checkStmt = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM Kep WHERE kepNev = :nev");
    oci_bind_by_name($checkStmt, ":nev", $kepNev);
    oci_execute($checkStmt);
    $checkRow = oci_fetch_assoc($checkStmt);

    if ($checkRow && $checkRow['CNT'] > 0) {
        $_SESSION['error_message'] = "Ez a képnév már létezik. Kérlek válassz másikat.";
        header("Location: palyazatra_jelentkezes.php?id=" . $_POST['palyazatID']);
        exit();
    }

    $helyID = null;
    if (!empty($orszag) && !empty($megye) && !empty($varos)) {
        $stmt = oci_parse($conn, "SELECT helyID FROM Hely WHERE varos = :city AND megye = :county AND orszag = :country");
        oci_bind_by_name($stmt, ":city", $varos);
        oci_bind_by_name($stmt, ":county", $megye);
        oci_bind_by_name($stmt, ":country", $orszag);
        oci_execute($stmt);
        if ($row = oci_fetch_assoc($stmt)) {
            $helyID = $row["HELYID"];
        } else {
            oci_free_statement($stmt);
            $stmt = oci_parse($conn, "INSERT INTO Hely (helyID, orszag, megye, varos) VALUES (hely_seq.NEXTVAL, :country, :county, :city) RETURNING helyID INTO :helyID");
            oci_bind_by_name($stmt, ":city", $varos);
            oci_bind_by_name($stmt, ":county", $megye);
            oci_bind_by_name($stmt, ":country", $orszag);
            oci_bind_by_name($stmt, ":helyID", $helyID, SQLT_INT);
            oci_execute($stmt);
        }
        oci_free_statement($stmt);
    }

    if (!empty($kepNev) && !empty($fIDUpload) && !empty($kategoriak)) {
        $stmt = oci_parse($conn, "INSERT INTO Kep (kepID, kepNev, fID, helyID) VALUES (kep_seq.NEXTVAL, :kepNev, :fID, :helyID) RETURNING kepID INTO :kepID");
        oci_bind_by_name($stmt, ":kepNev", $kepNev);
        oci_bind_by_name($stmt, ":fID", $fIDUpload);
        oci_bind_by_name($stmt, ":helyID", $helyID);
        oci_bind_by_name($stmt, ":kepID", $kepID, SQLT_INT);
        if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
            die("Adatbázis hiba: " . $e['message']);
        }
        oci_free_statement($stmt);

        if (isset($_FILES["uploadedFile"]) && $_FILES["uploadedFile"]["error"] == 0) {
            $uploadDir = "resources/APP_IMGS/";
            $fileExt = strtolower(pathinfo($_FILES["uploadedFile"]["name"], PATHINFO_EXTENSION));
            $uploadFile = $uploadDir . $kepNev . "." . $fileExt;
            $check = getimagesize($_FILES["uploadedFile"]["tmp_name"]);
            if (!$check) die("Nem kép fájl.");
            if (!move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $uploadFile)) {
                echo "Feltöltési hiba: " . htmlspecialchars($_FILES["uploadedFile"]["name"], ENT_QUOTES, 'UTF-8');
            }
        }

        // Pályázathoz rendelés
        if (!empty($_POST['palyazatID'])) {
            $palyazatID = intval($_POST['palyazatID']);
            $stmtNevezett = oci_parse($conn, "INSERT INTO Nevezett (kepID, pID) VALUES (:kepID, :pID)");
            oci_bind_by_name($stmtNevezett, ":kepID", $kepID);
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
        }

        // Kategóriák kezelése és kapcsolat mentése
        foreach ($kategoriak as $katNev) {
            $katNev = trim($katNev);
            if (empty($katNev)) continue;

            $katID = null;
            $katQuery = oci_parse($conn, "SELECT katID FROM Kategoria WHERE LOWER(kategoriaNev) = LOWER(:katNev)");
            oci_bind_by_name($katQuery, ":katNev", $katNev);
            oci_execute($katQuery);

            if ($row = oci_fetch_assoc($katQuery)) {
                $katID = $row['KATID'];
            } else {
                $insertKat = oci_parse($conn, "INSERT INTO Kategoria (katID, kategoriaNev) VALUES (kat_seq.NEXTVAL, :katNev) RETURNING katID INTO :katID");
                oci_bind_by_name($insertKat, ":katNev", $katNev);
                oci_bind_by_name($insertKat, ":katID", $katID, -1, OCI_B_INT);
                oci_execute($insertKat);
                oci_free_statement($insertKat);
            }
            oci_free_statement($katQuery);

            $insertKapcs = oci_parse($conn, "INSERT INTO KategoriaResze (katID, kepID) VALUES (:katID, :kepID)");
            oci_bind_by_name($insertKapcs, ":katID", $katID);
            oci_bind_by_name($insertKapcs, ":kepID", $kepID);
            oci_execute($insertKapcs);
            oci_free_statement($insertKapcs);

            $_SESSION['success_message'] = "$felhasznaloNev sikeresen jelentkeztél a '$palyazatNev' pályázatra!";
            header("Location: palyazatok.php");
            exit();
        }
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
    <title><?= htmlspecialchars($palyazatNev, ENT_QUOTES, 'UTF-8') ?> - Jelentkezés</title>
    <link rel="stylesheet" href="resources/CSS/upload.css">
    <link rel="stylesheet" href="resources/CSS/styles.css">
    <link rel="stylesheet" href="resources/CSS/navbar.css"
</head>
<body>
<?php include 'navbar.php'; ?>
<!--<script src="resources/JS/check_kepnev.js"></script>-->

<main>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="successMessage">
            <?php
            echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            alert("<?php echo addslashes($_SESSION['error_message']); ?>");
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="formHead">
            <h2>Fénykép feltöltése</h2>
            <?php if ($pID): ?>
                <h3>Jelentkezés a pályázatra: <?= $palyazatNev ?: "Ismeretlen pályázat"?></h3>

                <input type="hidden" name="palyazatID" value="<?php echo $pID; ?>">
            <?php endif; ?>
        </div>

        <div class="uploadForm">
            <div class="formElement">
                <label for="fileInput">Válassza ki a feltöltendő képet:</label>
                <input type="file" id="fileInput" name="uploadedFile" accept="image/*" required>
            </div>
            <div class="formElement">
                <label for="nameInput">Név:</label>
                <input id="nameInput" name="name" required>

                <label for="place">Hely:</label>
                <div>
                    <input list="countries" id="countryInput" name="countries" placeholder="Ország">
                    <datalist id="countries">
                        <?php foreach ($countries as $city): ?>
                            <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>

                    <input list="counties" id="countyInput" name="counties" placeholder="Megye">
                    <datalist id="counties">
                        <?php foreach ($counties as $county): ?>
                            <option value="<?= htmlspecialchars($county, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>

                    <input list="cities" id="cityInput" name="cities" placeholder="Város">
                    <datalist id="cities">
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <label for="categoryInput">Kategória:</label>
                <input list="categories" type="text" id="categoryInput" name="categories" placeholder="életkép" required>
                <datalist id="categories">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <button type="submit">Feltöltés</button>
            </div>
        </div>
    </form>
</main>
</body>
</html>
